<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Backend;

use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class AutoSuggester
{
    public function __construct(
        private readonly Packages $packages,
        private readonly NotificationCenter $notificationCenter,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function enableAutoSuggesterOnDca(string $table, string $notificationType): void
    {
        foreach ((array) $GLOBALS['TL_DCA'][$table]['fields'] as $field => $fieldConfig) {
            if (!isset($fieldConfig['nc_context'])) {
                continue;
            }

            $context = (string) ($fieldConfig['nc_context'] instanceof \BackedEnum ? $fieldConfig['nc_context']->value : $fieldConfig['nc_context']);

            // Disable browser autocompletion based on historic values for the token fields
            // as otherwise one would get two suggestions
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['autocomplete'] = false;

            if (version_compare(ContaoCoreBundle::getVersion(), '5.7', '>=')) {
                $this->addAssets($field, $notificationType, $context);
            } else {
                $this->addLegacyAssets($field, $notificationType, $context);
            }
        }
    }

    private function addAssets(string $field, string $notificationType, string $context): void
    {
        $GLOBALS['TL_CSS']['notification_center_autosuggester_css'] = $this->packages->getUrl('autosuggester.css','terminal42_notification_center');
        $GLOBALS['TL_JAVASCRIPT']['notification_center_autosuggester_css'] = $this->packages->getUrl('autosuggester.js','terminal42_notification_center');

        $GLOBALS['TL_MOOTOOLS'][] = \sprintf(
            '<script type="application/json" data-notification-center-auto-suggester>%s</script>',
            json_encode([
                'field' => sprintf('ctrl_%s', $field),
                'tokens' => $this->getTokenConfigForField($notificationType, $context),
            ]),
        );
    }

    // TODO: drop with support for Contao < 5.7
    private function addLegacyAssets(string $field, string $notificationType, string $context): void
    {
        $GLOBALS['TL_CSS']['notification_center_autosuggester_css'] = trim($this->packages->getUrl(
            'legacy/autosuggester.css',
            'terminal42_notification_center',
        ), '/');

        $GLOBALS['TL_MOOTOOLS']['notification_center_autosuggester_js'] = \sprintf(
            '<script src="%s"></script>',
            $this->packages->getUrl('legacy/autosuggester.js', 'terminal42_notification_center'),
        );

        $GLOBALS['TL_MOOTOOLS'][] = \sprintf(
            "<script>document.addEventListener('DOMContentLoaded',()=>{new initContaoNotificationCenterAutoSuggester('%s', %s)});</script>",
            'ctrl_'.$field,
            json_encode($this->getTokenConfigForField($notificationType, $context)),
        );
    }

    private function getTokenConfigForField(string $notificationType, string $context): array
    {
        $tokens = [];

        foreach ($this->notificationCenter->getTokenDefinitionsForNotificationType($notificationType, $context) as $token) {
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
                'name' => $token->getTokenName(),
                'label' => $label,
            ];
        }

        return $tokens;
    }
}
