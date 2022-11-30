<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Contao\CoreBundle\Util\LocaleUtil;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Event\ReceiptEvent;
use Terminal42\NotificationCenterBundle\Exception\InvalidNotificationTypeException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\ParcelCollection;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\NotificationConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;
use Terminal42\NotificationCenterBundle\Receipt\ReceiptCollection;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class NotificationCenter
{
    public function __construct(
        private Connection $connection,
        private NotificationTypeRegistry $notificationTypeRegistry,
        private GatewayRegistry $gatewayRegistry,
        private ConfigLoader $configLoader,
        private EventDispatcherInterface $eventDispatcher,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param array<string> $tokenDefinitionTypes
     *
     * @return array<TokenDefinitionInterface>
     */
    public function getTokenDefinitionsForNotificationType(string $typeName, array $tokenDefinitionTypes = []): array
    {
        $notificationType = $this->notificationTypeRegistry->getByName($typeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($typeName);
        }

        $event = new GetTokenDefinitionsEvent($notificationType, $notificationType->getTokenDefinitions());

        $this->eventDispatcher->dispatch($event);

        return $event->getTokenDefinitions($tokenDefinitionTypes);
    }

    /**
     * @return array<int, string>
     */
    public function getNotificationsForNotificationType(string $typeName): array
    {
        if (null === $this->notificationTypeRegistry->getByName($typeName)) {
            return [];
        }

        return $this->connection->createQueryBuilder()
            ->select('id', 'title')
            ->from('tl_nc_notification')
            ->where('type = :type')
            ->orderBy('title')
            ->setParameter('type', $typeName)
            ->executeQuery()
            ->fetchAllKeyValue()
        ;
    }

    /**
     * @param array<string, mixed> $rawTokens
     */
    public function createTokenCollectionFromArray(array $rawTokens, string $notificationTypeName): TokenCollection
    {
        $notificationType = $this->notificationTypeRegistry->getByName($notificationTypeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($notificationTypeName);
        }

        $tokenDefinitions = $this->getTokenDefinitionsForNotificationType($notificationTypeName);

        return TokenCollection::fromRawAndDefinitions($rawTokens, $tokenDefinitions);
    }

    public function createParcelsForNotification(int $id, TokenCollection $tokenCollection, string $locale = null): ParcelCollection
    {
        $parcels = new ParcelCollection();

        foreach ($this->configLoader->loadMessagesForNotification($id) as $messageConfig) {
            if (null !== ($parcel = $this->createParcelForMessage($messageConfig->getId(), $tokenCollection, $locale))) {
                $parcels->add($parcel);
            }
        }

        return $parcels;
    }

    /**
     * @param string|null $locale The locale for the message. Passing none will try to automatically take
     *                            the one of the current request.
     *
     * @throw CannotCreateParcelException
     */
    public function createParcelForMessage(int $id, TokenCollection $tokenCollection, string $locale = null): Parcel|null
    {
        if (null === ($messageConfig = $this->configLoader->loadMessage($id))) {
            throw CouldNotCreateParcelException::becauseOfNonExistentMessage($id);
        }

        // Create a parcel with the token collection stamp
        $parcel = new Parcel($messageConfig, [new TokenCollectionStamp($tokenCollection)]);

        // Add additional stamps
        if (null !== ($notificationConfig = $this->configLoader->loadNotification($messageConfig->getNotification()))) {
            $parcel = $parcel->withStamp(new NotificationConfigStamp($notificationConfig));
        }

        if (null !== ($gatewayConfig = $this->configLoader->loadGateway($messageConfig->getGateway()))) {
            $parcel = $parcel->withStamp(new GatewayConfigStamp($gatewayConfig));
        }

        if (
            null === $locale
            && ($request = $this->requestStack->getCurrentRequest())
            && ($pageModel = $request->attributes->get('pageModel'))
            && $pageModel instanceof PageModel
        ) {
            // We do not want to use $request->getLocale() here because this is never empty. If we're not on a Contao
            // page, $request->getLocale() would return the configured default locale which in Symfony always falls back
            // to English. But we want $locale to remain null in case we really have no Contao page language so that our
            // own fallback mechanism can kick in (loading the language marked as fallback by the user).
            $pageModel->loadDetails();
            $locale = $pageModel->language ? LocaleUtil::formatAsLocale($pageModel->language) : null;
        }

        if (null !== ($languageConfig = $this->configLoader->loadLanguageForMessageAndLocale($messageConfig->getId(), $locale))) {
            $parcel = $parcel->withStamp(new LanguageConfigStamp($languageConfig));
        }

        return $this->dispatchCreateParcelEvent($parcel);
    }

    /**
     * If you want to give third-party developers the chance to add stamps or modify your parcel using the
     * CreateParcelEvent event, you can manually do so by calling this method. Note that the event also allows
     * developers to disable delivery (e.g. based on day time, message settings, conditions etc.). In such a case,
     * this method will return null.
     */
    public function dispatchCreateParcelEvent(Parcel $parcel): Parcel|null
    {
        $event = new CreateParcelEvent($parcel);

        $this->eventDispatcher->dispatch($event);

        if (!$event->shouldDeliver()) {
            return null;
        }

        return $event->getParcel();
    }

    /**
     * @param string|null $gatewayName you can an either provide a gateway name directly or stick a GatewayConfigStamp
     *                                 on your parcel
     */
    public function sendParcel(Parcel $parcel, string $gatewayName = null): Receipt
    {
        if (null === $gatewayName && null !== ($gatewayStamp = $parcel->getStamp(GatewayConfigStamp::class))) {
            $gatewayName = $gatewayStamp->gatewayConfig->getType();
        }

        $gateway = $this->gatewayRegistry->getByName((string) $gatewayName);

        if (null === $gateway) {
            $receipt = Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseNoGatewayWasDefinedForParcel()
            );
        } else {
            $receipt = $gateway->sendParcel($parcel);
        }

        // Readonly event so developers can do whatever they want with the receipt, whether it was successful
        // or not. Use this to implement logging etc.
        $this->eventDispatcher->dispatch(new ReceiptEvent($receipt));

        return $receipt;
    }

    /**
     * Shortcut to send an entire set of messages that belong to the same notification.
     *
     * @param string|null $locale The locale for the message. Passing none will try to automatically take
     *                            the one of the current request.
     */
    public function sendNotification(int $id, TokenCollection $tokenCollection, string $locale = null): ReceiptCollection
    {
        $collection = new ReceiptCollection();

        foreach ($this->createParcelsForNotification($id, $tokenCollection, $locale) as $parcel) {
            $collection->add($this->sendParcel($parcel));
        }

        return $collection;
    }
}
