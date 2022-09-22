<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Exception\CouldNotSendNotificationException;
use Terminal42\NotificationCenterBundle\Exception\InvalidNotificationTypeException;
use Terminal42\NotificationCenterBundle\Gateway\GatewayRegistry;
use Terminal42\NotificationCenterBundle\Gateway\Parcel;
use Terminal42\NotificationCenterBundle\MessageType\MessageTypeRegistry;
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
     * @return array<TokenDefinitionInterface>
     */
    public function getTokenDefinitionsForType(string $typeName, array $tokenDefinitionTypes = []): array
    {
        $notificationType = $this->messageTypeRegistry->getByName($typeName);

        if (null === $notificationType) {
            throw InvalidNotificationTypeException::becauseTypeDoesNotExist($typeName);
        }

        $event = new GetTokenDefinitionsEvent($typeName, $notificationType->getTokenDefinitions());

        $this->eventDispatcher->dispatch($event);

        return $event->getTokenDefinitions($tokenDefinitionTypes);
    }

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

    public function createTokenCollectionFromArray(array $rawTokens, string $typeName): TokenCollection
    {
        $collection = new TokenCollection();
        $tokenDefinitions = $this->getTokenDefinitionsForType($typeName);

        foreach ($rawTokens as $rawTokenName => $rawTokenValue) {
            foreach ($tokenDefinitions as $definition) {
                if ($definition->matchesTokenName($rawTokenName)) {
                    $collection->add(new Token($definition, $rawTokenName, $rawTokenValue));
                }
            }
        }

        return $collection;
    }

    public function createParcelForNotification(): Parcel
    {

    }

    /**
     * @param string|null $locale The locale for the message. Passing none will try to automatically take
     *                            the one of the current request.
     *
     * @throws CouldNotSendNotificationException
     */
    public function sendNotification(int $id, TokenCollection $tokenCollection, string $locale = null): bool
    {
        $parcels = [];

        if (null === $locale && ($request = $this->requestStack->getCurrentRequest())) {
            $locale = $request->getLocale();
        }

        $notificationConfig = $this->configLoader->loadNotificationFromDatabase($id);

        if (null === $notificationConfig) {
            throw CouldNotSendNotificationException::becauseOfIdNotFound($id);
        }

        foreach ($this->getMessageIdsForNotification($notificationConfig->getId()) as $messageId) {
            $messageConfig = $this->configLoader->loadMessageFromDatabase($messageId);

            if (null === $messageConfig) {
                continue;
            }

            $gatewayConfig = $this->configLoader->loadGatewayFromDatabase($messageConfig->getGateway());

            if (null === $gatewayConfig) {
                continue;
            }

            $parcel = new Parcel($notificationConfig, $messageConfig, $gatewayConfig, $tokenCollection);

            $languageId = $this->getLanguageIdForMessageAndLocale($messageId, $locale);

            if (null !== $languageId && null !== ($languageConfig = $this->configLoader->loadLanguageFromDatabase($languageId))) {
                $parcel = $parcel->withLanguageConfig($languageConfig);
            }

            $parcels[] = $parcel;
        }

        foreach ($parcels as $parcel) {
            $gateway = $this->gatewayRegistry->getByName($parcel->gatewayConfig->getType());

            if (null === $gateway) {
                // We should not throw an error here. If you have removed a gateway later on and your config still
                // has a message referencing a non-existent gateway implementation, we just ignore that.
                continue;
            }

            $gateway->sendParcel($parcel); // TODO: result?
        }

        return true; // TODO: Convert to proper result object coming from the gateway
    }

    private function getMessageIdsForNotification(int $notificationId): array
    {
        return array_map(
            static fn (array $row) => $row['id'],
            $this->connection->createQueryBuilder()
                ->select('id')
                ->from('tl_nc_message')
                ->where('pid = :pid')
                ->setParameter('pid', $notificationId)
                ->fetchAllAssociative()
        );
    }

    private function getLanguageIdForMessageAndLocale(int $messageId, string $locale): int|null
    {
        $localeWithoutRegion = substr($locale, 0, 2);

        $id = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_nc_language')
            ->where('pid = :pid')
            ->andWhere('language = :locale OR language = :localeWithoutRegion OR fallback = :fallback')
            ->orderBy('LENGTH(language) DESC, fallback') // First the exact match with region, then without region, then fallback
            ->setParameter('pid', $messageId)
            ->setParameter('locale', $locale)
            ->setParameter('localeWithoutRegion', $localeWithoutRegion)
            ->setParameter('fallback', true)
            ->fetchOne()
        ;

        if (false === $id) {
            return null;
        }

        return (int) $id;
    }

    private function getSelectAliases(string $table, string $tableAlias): array
    {
        $select = [];
        $columns = $this->connection->createSchemaManager()->listTableColumns($table);

        foreach ($columns as $column) {
            $select[] = $tableAlias.'.'.$column->getName().' AS '.$tableAlias.'_'.$column->getName();
        }

        return $select;
    }
}
