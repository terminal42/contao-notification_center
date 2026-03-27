<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Backend;

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
            // as well as password manager tools
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['autocomplete'] = false;
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['data-1p-ignore'] = 'true';
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['data-lpignore'] = 'true';
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['data-bwignore'] = 'true';
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['data-controller'] = 'terminal42--autosuggester';
            $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['data-terminal42--autosuggester-tokens-value'] = json_encode($this->getTokenConfigForField($notificationType, $context));

            $GLOBALS['TL_CSS']['notification_center_autosuggester_css'] = $this->packages->getUrl('autosuggester.css', 'terminal42_notification_center');
            $GLOBALS['TL_JAVASCRIPT']['notification_center_autosuggester_css'] = $this->packages->getUrl('autosuggester.js', 'terminal42_notification_center');
        }
    }

    /**
     * @return array<array<string, string>>
     */
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
