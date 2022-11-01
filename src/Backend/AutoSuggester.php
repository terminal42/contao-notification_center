<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Backend;

use Symfony\Component\Asset\Packages;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class AutoSuggester
{
    public function __construct(private Packages $packages, private NotificationCenter $notificationCenter, private TranslatorInterface $translator)
    {
    }

    public function enableAutoSuggesterOnDca(string $table, string $notificationType): void
    {
        foreach ((array) $GLOBALS['TL_DCA'][$table]['fields'] as $field => $fieldConfig) {
            if (!isset($fieldConfig['nc_token_types'])) {
                continue;
            }

            // Disable browser autocompletion based on historic values for the token fields as otherwise
            // one would get two suggestions
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['autocomplete'] = false;

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
                $this->getTokenConfigForField($notificationType, $fieldConfig['nc_token_types'])
            );
        }
    }

    /**
     * @param array<string> $tokenDefinitionTypes
     */
    private function getTokenConfigForField(string $messageType, array $tokenDefinitionTypes): string
    {
        $tokens = [];

        foreach ($this->notificationCenter->getTokenDefinitionsForMessageType($messageType, $tokenDefinitionTypes) as $token) {
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

        return json_encode($tokens);
    }
}
