<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Event\LoadConfigEvent;
use Terminal42\NotificationCenterBundle\Exception\InvalidConfigException;

class ConfigLoader
{
    public function __construct(private Connection $connection, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function loadGatewayFromDatabase(int $id): GatewayConfig|null
    {
        return $this->loadFromDatabase($id, 'tl_nc_gateway', GatewayConfig::class);
    }

    public function loadNotificationFromDatabase(int $id): NotificationConfig|null
    {
        return $this->loadFromDatabase($id, 'tl_nc_notification', NotificationConfig::class);
    }

    public function loadMessageFromDatabase(int $id): MessageConfig|null
    {
        return $this->loadFromDatabase($id, 'tl_nc_message', MessageConfig::class);
    }

    public function loadLanguageFromDatabase(int $id): LanguageConfig|null
    {
        return $this->loadFromDatabase($id, 'tl_nc_language', LanguageConfig::class);
    }

    /**
     * @template T of AbstractConfig
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function loadFromDatabase(int|string $id, string $table, string $className): object|null
    {
        try {
            $parameters = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($table)
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative()
            ;

            if (false === $parameters) {
                return null;
            }

            return $this->loadFromArray($parameters, $className);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @template T of AbstractConfig
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    public function loadFromArray(array $parameters, string $className): AbstractConfig
    {
        if (!is_a($className, AbstractConfig::class, true)) {
            throw InvalidConfigException::becauseItDoesNotExtendAbstractConfig($className);
        }

        $event = new LoadConfigEvent($className::fromArray($parameters));

        $this->eventDispatcher->dispatch($event);

        return $event->config;
    }
}
