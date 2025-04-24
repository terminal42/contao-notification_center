<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

use Contao\DcaLoader;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Service\ResetInterface;

class ConfigLoader implements ResetInterface
{
    /**
     * @var array<string, array<int, array<string, mixed>>>
     */
    private array $cache = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function loadGateway(int $id): GatewayConfig|null
    {
        return $this->loadConfig($id, 'tl_nc_gateway', GatewayConfig::class);
    }

    public function loadNotification(int $id): NotificationConfig|null
    {
        return $this->loadConfig($id, 'tl_nc_notification', NotificationConfig::class);
    }

    public function loadMessage(int $id): MessageConfig|null
    {
        return $this->loadConfig($id, 'tl_nc_message', MessageConfig::class);
    }

    public function loadModule(int $id): ModuleConfig|null
    {
        return $this->loadConfig($id, 'tl_module', ModuleConfig::class);
    }

    public function loadForm(int $id): FormConfig|null
    {
        return $this->loadConfig($id, 'tl_form', FormConfig::class);
    }

    /**
     * @return array<MessageConfig>
     */
    public function loadMessagesForNotification(int $notificationId): array
    {
        $messages = [];

        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('tl_nc_message')
            ->where('pid = :pid')
            ->setParameter('pid', $notificationId)
        ;

        foreach ($query->fetchAllAssociative() as $row) {
            $row = $this->cleanParameters($row, 'tl_nc_message');

            $this->cache['tl_nc_message'][$row['id']] = $row;

            $messages[] = MessageConfig::fromArray($row);
        }

        return $messages;
    }

    public function loadLanguage(int $id): LanguageConfig|null
    {
        return $this->loadConfig($id, 'tl_nc_language', LanguageConfig::class);
    }

    public function loadLanguageForMessageAndLocale(int $messageId, string|null $locale = null): LanguageConfig|null
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('tl_nc_language')
            ->where('pid = :pid')
            ->setParameter('pid', $messageId)
        ;

        if (null === $locale) {
            $query
                ->andWhere('fallback = :fallback')
                ->setParameter('fallback', true)
            ;
        } else {
            $localeWithoutRegion = substr($locale, 0, 2);
            $query
                ->andWhere('language = :locale OR language = :localeWithoutRegion OR fallback = :fallback')
                ->orderBy('LENGTH(language) DESC, fallback') // First the exact match with region, then without region, then fallback
                ->setParameter('locale', $locale)
                ->setParameter('localeWithoutRegion', $localeWithoutRegion)
                ->setParameter('fallback', true)
            ;
        }

        $parameters = $query->fetchAssociative();

        if (false === $parameters) {
            return null;
        }

        $parameters = $this->cleanParameters($parameters, 'tl_nc_language');

        return LanguageConfig::fromArray($parameters);
    }

    public function reset(): void
    {
        $this->cache = [];
    }

    /**
     * @template T of AbstractConfig
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    private function loadConfig(int $id, string $table, string $className): AbstractConfig|null
    {
        if (null === ($parameters = $this->loadParameters($id, $table))) {
            return null;
        }

        return $className::fromArray($parameters);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadParameters(int $id, string $table): array|null
    {
        if (isset($this->cache[$table][$id])) {
            return $this->cache[$table][$id];
        }

        if (!isset($this->cache[$table])) {
            $this->cache[$table] = [];
        }

        try {
            $parameters = $this->connection->createQueryBuilder()
                ->select('*')
                ->from($table)
                ->where('id = :id')
                ->setParameter('id', $id)
                ->fetchAssociative()
            ;

            if (false === $parameters) {
                return $this->cache[$table][$id] = null;
            }

            $parameters = $this->cleanParameters($parameters, $table);

            return $this->cache[$table][$id] = $parameters;
        } catch (\Exception) {
            return $this->cache[$table][$id] = null;
        }
    }

    /**
     * @param array<mixed> $parameters
     *
     * @return array<mixed>
     */
    private function cleanParameters(array $parameters, string $table): array
    {
        $cleanedParameters = [];

        try {
            $dcaLoader = new DcaLoader($table);
            $dcaLoader->load();

            foreach ($parameters as $field => $parameter) {
                $cleanedParameters[$field] = $parameter;

                if (($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['decodeEntities'] ?? false) && \is_string($parameter)) {
                    $cleanedParameters[$field] = StringUtil::decodeEntities($parameter);
                }
            }
        } catch (\Exception) {
            // noop
        }

        return $cleanedParameters;
    }
}
