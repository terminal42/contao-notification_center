<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Intl\Locales;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\Backend\AutoSuggester;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;

class LanguageListener
{
    use GetCurrentRecordTrait;

    public function __construct(
        private AutoSuggester $autoSuggester,
        private Connection $connection,
        private ConfigLoader $configLoader,
        private Locales $locales,
        private TranslatorInterface $translator,
        private Security $security,
    ) {
    }

    #[AsCallback(table: 'tl_nc_language', target: 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        if (
            null === ($language = $this->configLoader->loadLanguage((int) $dc->id))
            || null === ($message = $this->configLoader->loadMessage($language->getMessage()))
            || null === ($gateway = $this->configLoader->loadGateway($message->getGateway()))
        ) {
            return;
        }

        if (isset($GLOBALS['TL_DCA']['tl_nc_language']['palettes'][$gateway->getType()])) {
            $GLOBALS['TL_DCA']['tl_nc_language']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_nc_language']['palettes'][$gateway->getType()];
        }

        if (($user = $this->security->getUser()) instanceof BackendUser) {
            $GLOBALS['TL_DCA']['tl_nc_language']['fields']['language']['default'] = $user->language;
        }

        if (
            null !== ($notification = $this->configLoader->loadNotification($message->getNotification()))
            && ($type = $notification->getType())
        ) {
            $this->autoSuggester->enableAutoSuggesterOnDca('tl_nc_language', $type);
        }
    }

    /**
     * @return array<string>
     */
    #[AsCallback(table: 'tl_nc_language', target: 'fields.language.options')]
    public function onLanguageOptionsCallback(): array
    {
        return $this->locales->getLocales();
    }

    #[AsCallback(table: 'tl_nc_language', target: 'fields.language.save_callback')]
    public function onSaveLanguage(mixed $value, DataContainer $dc): mixed
    {
        $check = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_nc_language')
            ->where('pid = :pid')
            ->andWhere('language = :language')
            ->andWhere('id != :id')
            ->setParameter('pid', $this->getCurrentRecord($dc)['pid'])
            ->setParameter('language', (string) $value)
            ->setParameter('id', (int) $dc->id)
            ->fetchOne()
        ;

        if (false !== $check) {
            throw new \InvalidArgumentException($this->translator->trans('ERR.unique', [$dc->field], 'contao_default'));
        }

        return $value;
    }

    #[AsCallback(table: 'tl_nc_language', target: 'fields.fallback.save_callback')]
    public function onSaveFallback(mixed $value, DataContainer $dc): mixed
    {
        if (!$value) {
            return $value;
        }

        $existingId = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('tl_nc_language')
            ->where('pid = :pid')
            ->andWhere('fallback = :fallback')
            ->andWhere('id != :id')
            ->setParameter('pid', $this->getCurrentRecord($dc)['pid'])
            ->setParameter('fallback', true)
            ->setParameter('id', (int) $dc->id)
            ->fetchOne()
        ;

        if (false !== $existingId) {
            $this->connection->update('tl_nc_language', ['fallback' => false], ['id' => $existingId], ['fallback' => Types::BOOLEAN]);
        }

        // Reset config caches just to be sure
        $this->configLoader->reset();

        return $value;
    }
}
