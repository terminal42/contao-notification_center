<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Intl\Locales;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class LanguageListener
{
    use OverrideDefaultPaletteTrait;

    public function __construct(private Connection $connection, private Locales $locales, private TranslatorInterface $translator, private Packages $packages, private NotificationCenter $notificationCenter)
    {
    }

    #[AsCallback(table: 'tl_nc_language', target: 'config.onload')]
    public function onLoadCallback(DataContainer $dc): void
    {
        $currentRecord = $this->getCurrentRecord($dc);
        $messageRecord = $this->queryRecord('tl_nc_message', (int) $currentRecord['pid']);

        if ([] === $messageRecord) {
            return;
        }

        $notificationRecord = $this->queryRecord('tl_nc_notification', (int) $messageRecord['pid']);

        if ([] === $notificationRecord) {
            return;
        }

        $this->overrideDefaultPaletteForGateway((int) $messageRecord['gateway'], 'tl_nc_language');

        foreach ((array) $GLOBALS['TL_DCA']['tl_nc_language']['fields'] as $field => $fieldConfig) {
            if (!isset($fieldConfig['nc_token_types'])) {
                continue;
            }

            // TODO: put this in a template, man
            // Disable browser autocompletion based on historic values for the token fields as otherwise
            // one would get two suggestions
            $GLOBALS['TL_DCA']['tl_nc_language']['fields'][$field]['eval']['autocomplete'] = false;

            $GLOBALS['TL_CSS']['notification_center_autosuggester_css'] = trim($this->packages->getUrl(
                'autosuggester.css',
                'terminal42_notification_center'
            ), '/');
            $GLOBALS['TL_MOOTOOLS']['notification_center_autosuggester_js'] = sprintf(
                '<script src="%s"></script>',
                $this->packages->getUrl('autosuggester.js', 'terminal42_notification_center')
            );

            $GLOBALS['TL_MOOTOOLS'][] = sprintf(
                "<script>(function(window){new window.ContaoNotificationCenterAutoSuggester('%s', %s)})(window);</script>",
                'ctrl_'.$field,
                $this->getTokenConfigForField($notificationRecord['type'], $fieldConfig['nc_token_types'])
            );
        }
    }

    private function getTokenConfigForField(string $messageType, array $tokenTypes): string
    {
        $tokens = [];

        foreach ($this->notificationCenter->getTokenDefinitionsForMessageType($messageType, $tokenTypes) as $token) {
            $label = '';

            if (($translationKey = $token->getTranslationKey()) !== null) {
                $translationKey = 'nc_tokens.'.$translationKey;
                $label = $this->translator->trans($translationKey, [], 'contao_nc_tokens');

                // Missing label would return the key untranslated, we ignore in that case
                if ($label === $translationKey) {
                    $label = '';
                }
            }

            $tokens[] = [
                'name' => $token->getName(),
                'label' => $label,
            ];
        }

        return json_encode($tokens);
    }

    #[AsCallback(table: 'tl_nc_language', target: 'fields.language.options')]
    public function onLanguageOptionsCallback(): array
    {
        return $this->locales->getLocales();
    }

    #[AsCallback(table: 'tl_nc_language', target: 'fields.language.save_callback')]
    public function onSaveLanguage($value, DataContainer $dc)
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
            throw new \Exception($this->translator->trans('ERR.unique', [$dc->field], 'contao_default'));
        }

        return $value;
    }

    #[AsCallback(table: 'tl_nc_language', target: 'fields.fallback.save_callback')]
    public function onSaveFallback($value, DataContainer $dc)
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

        return $value;
    }
}
