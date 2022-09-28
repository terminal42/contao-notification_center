<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Contao\CoreBundle\Util\LocaleUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Exception\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\Exception\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Exception\InvalidNotificationTypeException;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\GatewayConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\NotificationConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;
use Terminal42\NotificationCenterBundle\Token\Token;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class NotificationCenter
{
    public function __construct(
        private Connection $connection,
        private MessageTypeRegistry $messageTypeRegistry,
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
    public function getTokenDefinitionsForMessageType(string $typeName, array $tokenDefinitionTypes = []): array
    {
        $messageType = $this->messageTypeRegistry->getByName($typeName);

        if (null === $messageType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($typeName);
        }

        $event = new GetTokenDefinitionsEvent($messageType, $messageType->getTokenDefinitions());

        $this->eventDispatcher->dispatch($event);

        return $event->getTokenDefinitions($tokenDefinitionTypes);
    }

    /**
     * @return array<int, string>
     */
    public function getNotificationsForMessageType(string $typeName): array
    {
        if (null === $this->messageTypeRegistry->getByName($typeName)) {
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
    public function createTokenCollectionFromArray(array $rawTokens, string $messageTypeName): TokenCollection
    {
        $messageType = $this->messageTypeRegistry->getByName($messageTypeName);

        if (null === $messageType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($messageTypeName);
        }

        $collection = new TokenCollection();
        $tokenDefinitions = $this->getTokenDefinitionsForMessageType($messageTypeName);

        foreach ($rawTokens as $rawTokenName => $rawTokenValue) {
            foreach ($tokenDefinitions as $definition) {
                if ($definition->matchesTokenName($rawTokenName)) {
                    $collection->add(new Token($definition, $rawTokenName, $rawTokenValue));
                }
            }
        }

        return $collection;
    }

    /**
     * @throw CannotCreateParcelException
     *
     * @return array<Parcel>
     */
    public function createParcelsForNotification(int $id, TokenCollection $tokenCollection, string $locale = null): array
    {
        $parcels = [];

        foreach ($this->configLoader->loadMessagesForNotification($id) as $messageConfig) {
            $parcels[] = $this->createParcelForMessage($messageConfig->getId(), $tokenCollection, $locale);
        }

        return $parcels;
    }

    /**
     * @param string|null $locale The locale for the message. Passing none will try to automatically take
     *                            the one of the current request.
     *
     * @throw CannotCreateParcelException
     */
    public function createParcelForMessage(int $id, TokenCollection $tokenCollection, string $locale = null): Parcel
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
        ) {
            // We do not want to use $request->getLocale() here because this is never empty. If we're not on a Contao
            // page, $request->getLocale() would return the configured default locale which in Symfony always falls back
            // to English. But we want $locale to remain null in case we really have no Contao page language so that our
            // own fallback mechanism can kick in (loading the language marked as fallback by the user).
            $locale = $pageModel->language ? LocaleUtil::formatAsLocale($pageModel->language) : null;
        }

        if (null !== ($languageConfig = $this->configLoader->loadLanguageForMessageAndLocale($messageConfig->getId(), $locale))) {
            $parcel = $parcel->withStamp(new LanguageConfigStamp($languageConfig));
        }

        // TODO: Should dispatching this event happen in a separate method so it's easy for developers to call the
        // event themselves, if the parcel is built manually?
        $event = new CreateParcelEvent($parcel);

        $this->eventDispatcher->dispatch($event);

        // TODO: Should be nullable, maybe? So that we can disable sending a message using the publish field, conditions
        // and the event?
        return $event->getParcel();
    }

    /**
     * @param string|null $gatewayName you can an either provide a gateway name directly or stick a GatewayConfigStamp
     *                                 on your parcel
     *
     * @throws CouldNotDeliverParcelException
     */
    public function sendParcel(Parcel $parcel, string $gatewayName = null): bool
    {
        if (null === $gatewayName && null !== ($gatewayStamp = $parcel->getStamp(GatewayConfigStamp::class))) {
            $gatewayName = $gatewayStamp->gatewayConfig->getType();
        }

        $gateway = $this->gatewayRegistry->getByName((string) $gatewayName);

        if (null === $gateway) {
            throw CouldNotDeliverParcelException::becauseNoGatewayWasDefinedForParcel();
        }

        $gateway->sendParcel($parcel); // TODO: result?

        return true;
    }

    /**
     * Shortcut to send an entire set of messages that belong to the same notification.
     *
     * @param string|null $locale The locale for the message. Passing none will try to automatically take
     *                            the one of the current request.
     *
     * @throws CouldNotCreateParcelException
     * @throws CouldNotDeliverParcelException
     */
    public function sendNotification(int $id, TokenCollection $tokenCollection, string $locale = null): bool
    {
        foreach ($this->createParcelsForNotification($id, $tokenCollection, $locale) as $parcel) {
            $this->sendParcel($parcel); // TODO result?
        }

        return true; // TODO: Convert to proper result object coming from the gateway
    }
}
