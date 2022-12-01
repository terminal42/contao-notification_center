<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsHook('processFormData')]
class ProcessFormDataListener
{
    public function __construct(private NotificationCenter $notificationCenter)
    {
    }

    /**
     * @param array<string, mixed> $submittedData
     * @param array<string, mixed>$formData
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>      $labels
     */
    public function __invoke(array $submittedData, array $formData, array|null $files, array $labels, Form $form): void
    {
        if (!isset($formData['nc_notification']) || !is_numeric($formData['nc_notification'])) {
            return;
        }

        $tokens = [];
        $rawData = [];
        $rawDataFilled = [];

        foreach ($submittedData as $k => $v) {
            $label = $labels[$k] ?? ucfirst($k);

            $tokens['formlabel_'.$k] = $label;
            $tokens['form_'.$k] = $v;
            $rawData[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);

            if (\is_array($v) || \strlen($v)) {
                $rawDataFilled[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);
            }
        }

        foreach ($formData as $k => $v) {
            $tokens['formconfig_'.$k] = $v;
        }

        $tokens['raw_data'] = implode("\n", $rawData);
        $tokens['raw_data_filled'] = implode("\n", $rawDataFilled);

        foreach ($files as $k => $file) {
            $tokens['form_'.$k] = $file;
        }

        $this->notificationCenter->sendNotification((int) $formData['nc_notification'], $tokens);
    }
}
