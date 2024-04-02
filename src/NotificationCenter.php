<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Codefog\HasteBundle\StringParser;
use Contao\CoreBundle\Util\LocaleUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionClassesForContextEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\Event\ReceiptEvent;
use Terminal42\NotificationCenterBundle\Exception\InvalidNotificationTypeException;
use Terminal42\NotificationCenterBundle\Exception\InvalidTokenException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotDeliverParcelException;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotSealParcelException;
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
use Terminal42\NotificationCenterBundle\Token\Token;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;
use Terminal42\NotificationCenterBundle\Token\TokenContext;

class NotificationCenter
{
    public function __construct(
        private readonly Connection $connection,
        private readonly NotificationTypeRegistry $notificationTypeRegistry,
        private readonly GatewayRegistry $gatewayRegistry,
        private readonly ConfigLoader $configLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly BulkyItemStorage $bulkyGoodsStorage,
        private readonly StringParser $stringParser,
    ) {
    }

    public function getBulkyGoodsStorage(): BulkyItemStorage
    {
        return $this->bulkyGoodsStorage;
    }

    /**
     * @return array<TokenDefinitionInterface>
     */
    public function getTokenDefinitionsForNotificationType(string $typeName, string $context = ''): array
    {
        $notificationType = $this->notificationTypeRegistry->getByName($typeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($typeName);
        }

        $event = new GetTokenDefinitionsForNotificationTypeEvent($notificationType, $notificationType->getTokenDefinitions());

        $this->eventDispatcher->dispatch($event);

        if ('' !== $context) {
            return $event->getTokenDefinitions($this->getValidTokenDefinitionClassesForContext($context));
        }

        return $event->getTokenDefinitions();
    }

    /**
     * @return array<class-string<TokenDefinitionInterface>>
     */
    public function getValidTokenDefinitionClassesForContext(string $context): array
    {
        $event = new GetTokenDefinitionClassesForContextEvent($context, (array) TokenContext::tryFrom($context)?->definitions());

        $this->eventDispatcher->dispatch($event);

        return $event->getTokenDefinitionClasses();
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
    public function createTokenCollectionFromArray(array $rawTokens, string $notificationTypeName, StampCollection|null $stamps = null): TokenCollection
    {
        $notificationType = $this->notificationTypeRegistry->getByName($notificationTypeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($notificationTypeName);
        }

        $tokenDefinitions = $this->getTokenDefinitionsForNotificationType($notificationTypeName);

        $collection = new TokenCollection();

        $flattenedTokens = [];

        foreach ($rawTokens as $rawTokenName => $rawTokenValue) {
            $this->stringParser->flatten($rawTokenValue, $rawTokenName, $flattenedTokens);
        }

        foreach (array_merge($flattenedTokens, $rawTokens) as $rawTokenName => $rawTokenValue) {
            $addedByDefinition = false;

            foreach ($tokenDefinitions as $definition) {
                if ($definition->matches($rawTokenName, $rawTokenValue)) {
                    $collection->add($definition->createToken($rawTokenName, $rawTokenValue, $stamps));
                    $addedByDefinition = true;
                    break;
                }
            }

            if (!$addedByDefinition) {
                $collection->add(Token::fromValue($rawTokenName, $rawTokenValue));
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

        $parcel = new Parcel($messageConfig);

        foreach ($stamps->all() as $stamp) {
            $parcel = $parcel->withStamp($stamp);
        }

        // Add potentially missing stamps
        if (!$parcel->hasStamp(NotificationConfigStamp::class)) {
            if (null !== ($notificationConfig = $this->configLoader->loadNotification($messageConfig->getNotification()))) {
                $parcel = $parcel->withStamp(new NotificationConfigStamp($notificationConfig));
            }
        }

        if (!$parcel->hasStamp(GatewayConfigStamp::class)) {
            if (null !== ($gatewayConfig = $this->configLoader->loadGateway($messageConfig->getGateway()))) {
                $parcel = $parcel->withStamp(new GatewayConfigStamp($gatewayConfig));
            }
        }

        /** @var LocaleStamp|null $localeStamp */
        $localeStamp = $parcel->getStamp(LocaleStamp::class);
        $locale = $localeStamp?->locale;

        if (
            null !== ($languageConfig = $this->configLoader->loadLanguageForMessageAndLocale(
                $messageConfig->getId(),
                $locale,
            ))
        ) {
            $parcel = $parcel->withStamp(new LanguageConfigStamp($languageConfig));
        }

        return $this->dispatchCreateParcelEvent($parcel);
    }

    /**
     * If you want to give third-party developers the chance to add stamps or modify
     * your parcel using the CreateParcelEvent event, you can manually do so by
     * calling this method. Note that the event also allows developers to disable
     * delivery (e.g. based on day time, message settings, conditions etc.). In such a
     * case, this method will return null.
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
     * Checks for a GatewayConfigStamp on the parcel and returns the matching gateway
     * if present.
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
     * @throws CouldNotSealParcelException
     */
    public function sendParcel(Parcel $parcel, string|null $gatewayName = null): Receipt
    {
        if (null === $gatewayName) {
            $gateway = $this->getGatewayForParcel($parcel);
        } else {
            $gateway = $this->gatewayRegistry->getByName($gatewayName);
        }

        if (null === $gateway) {
            $receipt = Receipt::createForUnsuccessfulDelivery(
                $parcel,
                CouldNotDeliverParcelException::becauseNoGatewayWasDefinedForParcel(),
            );

            // Readonly event so developers can do whatever they want with the receipt,
            // whether it was successful or not. Use this to implement logging etc.
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
        // are expected to build parcels and stamps with proper serializable data
        // in GatewayInterface::sealParcel().
        $parcel = Parcel::fromSerialized($parcel->serialize());

        $receipt = $gateway->sendParcel($parcel);

        // Readonly event so developers can do whatever they want with the receipt,
        // whether it was successful or not. Use this to implement logging etc.
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
    public function sendNotification(int $id, TokenCollection|array $tokens, string|null $locale = null): ReceiptCollection
    {
        return $this->sendNotificationWithStamps($id, $this->createBasicStampsForNotification($id, $tokens, $locale));
    }

    public function sendNotificationWithStamps(int $id, StampCollection $stamps): ReceiptCollection
    {
        $collection = new ReceiptCollection();

        foreach ($this->createParcelsForNotification($id, $stamps) as $parcel) {
            $collection->add($this->sendParcel($parcel));
        }

        return $collection;
    }

    public function createBasicStampsForNotification(int $id, TokenCollection|array $tokens, string|null $locale = null): StampCollection
    {
        $notificationConfig = $this->configLoader->loadNotification($id);

        if (!$notificationConfig instanceof NotificationConfig) {
            throw CouldNotCreateParcelException::becauseOfNonExistentMessage($id);
        }

        $stamps = new StampCollection([new NotificationConfigStamp($notificationConfig)]);

        if (null === $locale && ($request = $this->requestStack->getCurrentRequest()) instanceof Request) {
            $stamps = $stamps
                ->with(new LocaleStamp(LocaleUtil::formatAsLocale($request->getLocale())))
            ;
        }

        if (!$tokens instanceof TokenCollection) {
            $tokens = $this->createTokenCollectionFromArray($tokens, $notificationConfig->getType(), $stamps);
        }

        return $stamps->with(new TokenCollectionStamp($tokens));
    }
}
