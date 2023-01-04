<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Contao\CoreBundle\Util\LocaleUtil;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Event\ReceiptEvent;
use Terminal42\NotificationCenterBundle\Exception\InvalidNotificationTypeException;
use Terminal42\NotificationCenterBundle\Exception\InvalidTokenException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotFinalizeParcelException;
use Terminal42\NotificationCenterBundle\Gateway\GatewayInterface;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeRegistry;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\ParcelCollection;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LocaleStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\NotificationConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;
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
        private BulkyItemStorage $bulkyGoodsStorage,
    ) {
    }

    public function getBulkyGoodsStorage(): BulkyItemStorage
    {
        return $this->bulkyGoodsStorage;
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
     *
     * @throws InvalidTokenException
     */
    public function createTokenCollectionFromArray(array $rawTokens, string $notificationTypeName): TokenCollection
    {
        $notificationType = $this->notificationTypeRegistry->getByName($notificationTypeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($notificationTypeName);
        }

        $tokenDefinitions = $this->getTokenDefinitionsForNotificationType($notificationTypeName);

        $collection = new TokenCollection();

        foreach ($rawTokens as $rawTokenName => $rawTokenValue) {
            foreach ($tokenDefinitions as $definition) {
                if ($definition->matchesTokenName($rawTokenName)) {
                    $token = $definition->createToken($rawTokenName, $rawTokenValue);
                    $collection->add($token);
                }
            }
        }

        return $collection;
    }

    public function createParcelsForNotification(int $id, StampCollection $stamps): ParcelCollection
    {
        $parcels = new ParcelCollection();

        foreach ($this->configLoader->loadMessagesForNotification($id) as $messageConfig) {
            if (null !== ($parcel = $this->createParcelForMessage($messageConfig->getId(), $stamps))) {
                $parcels->add($parcel);
            }
        }

        return $parcels;
    }

    /**
     * @throws CouldNotCreateParcelException in case the message ID does not exist
     */
    public function createParcelForMessage(int $id, StampCollection $stamps): Parcel|null
    {
        if (null === ($messageConfig = $this->configLoader->loadMessage($id))) {
            throw CouldNotCreateParcelException::becauseOfNonExistentMessage($id);
        }

        $parcel = new Parcel($messageConfig, $stamps);

        // Add additional stamps
        if (null !== ($notificationConfig = $this->configLoader->loadNotification($messageConfig->getNotification()))) {
            $parcel = $parcel->withStamp(new NotificationConfigStamp($notificationConfig));
        }

        if (null !== ($gatewayConfig = $this->configLoader->loadGateway($messageConfig->getGateway()))) {
            $parcel = $parcel->withStamp(new GatewayConfigStamp($gatewayConfig));
        }

        /** @var LocaleStamp|null $localeStamp */
        $localeStamp = $parcel->getStamp(LocaleStamp::class);
        $locale = $localeStamp?->locale;

        if (
            null !== ($languageConfig = $this->configLoader->loadLanguageForMessageAndLocale(
                $messageConfig->getId(),
                $locale
            ))
        ) {
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
     * Checks for a GatewayConfigStamp on the parcel and returns the matching gateway if present.
     */
    public function getGatewayForParcel(Parcel $parcel): GatewayInterface|null
    {
        if (null === ($gatewayStamp = $parcel->getStamp(GatewayConfigStamp::class))) {
            return null;
        }

        $gatewayName = $gatewayStamp->gatewayConfig->getType();

        return $this->gatewayRegistry->getByName($gatewayName);
    }

    /**
     * @param string|null $gatewayName you can an either provide a gateway name directly or stick a GatewayConfigStamp
     *                                 on your parcel
     *
     * @throws CouldNotFinalizeParcelException
     */
    public function sendParcel(Parcel $parcel, string $gatewayName = null): Receipt
    {
        if (null === $gatewayName) {
            $gateway = $this->getGatewayForParcel($parcel);
        } else {
            $gateway = $this->gatewayRegistry->getByName($gatewayName);
        }

        if (null === $gateway) {
            $receipt = Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseNoGatewayWasDefinedForParcel()
            );

            // Readonly event so developers can do whatever they want with the receipt, whether it was successful
            // or not. Use this to implement logging etc.
            $this->eventDispatcher->dispatch(new ReceiptEvent($receipt));

            return $receipt;
        }

        // Seal if not already sealed
        if (!$parcel->isSealed()) {
            $parcel = $gateway->sealParcel($parcel);

            // Gateways are expected to seal but let's be very sure it is
            $parcel = $parcel->seal();
        }

        // We force serialization here in order to prevent usage errors. Developers
        // are expected to build parcels and stamps with proper serializable data in
        // GatewayInterface::sealParcel().
        $parcel = Parcel::fromSerialized($parcel->serialize());

        $receipt = $gateway->sendParcel($parcel);

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
     *
     * @throws CouldNotCreateParcelException in case the notification ID does not exist
     */
    public function sendNotification(int $id, TokenCollection|array $tokens, string $locale = null): ReceiptCollection
    {
        return $this->sendNotificationWithStamps($id, $this->createTokenAndLocaleStampsForNotification($id, $tokens, $locale));
    }

    public function sendNotificationWithStamps(int $id, StampCollection $stamps): ReceiptCollection
    {
        $collection = new ReceiptCollection();

        foreach ($this->createParcelsForNotification($id, $stamps) as $parcel) {
            $collection->add($this->sendParcel($parcel));
        }

        return $collection;
    }

    public function createTokenAndLocaleStampsForNotification(int $id, TokenCollection|array $tokens, string $locale = null): StampCollection
    {
        $stamps = new StampCollection();

        if (!$tokens instanceof TokenCollection) {
            $notificationConfig = $this->configLoader->loadNotification($id);

            if (!$notificationConfig instanceof NotificationConfig) {
                throw CouldNotCreateParcelException::becauseOfNonExistentMessage($id);
            }

            $tokens = $this->createTokenCollectionFromArray($tokens, $notificationConfig->getType());
        }

        $stamps = $stamps->with(new TokenCollectionStamp($tokens));

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

            if ($pageModel->language) {
                $stamps = $stamps
                    ->with(new LocaleStamp(LocaleUtil::formatAsLocale($pageModel->language)))
                ;
            }
        }

        return $stamps;
    }
}
