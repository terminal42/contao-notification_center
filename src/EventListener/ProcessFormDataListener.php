<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;

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
        $bulkyItemVouchers = [];
        $files = !\is_array($files) ? [] : $files; // In Contao 4.13, $files can be null

        foreach ($submittedData as $k => $v) {
            $label = $labels[$k] ?? ucfirst($k);

            $tokens['formlabel_'.$k] = $label;
            $tokens['form_'.$k] = $v;
            $rawData[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);

            if (\is_array($v) || ('' !== (string) $v)) {
                $rawDataFilled[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);
            }
        }

        foreach ($formData as $k => $v) {
            $tokens['formconfig_'.$k] = $v;
        }

        $tokens['raw_data'] = implode("\n", $rawData);
        $tokens['raw_data_filled'] = implode("\n", $rawDataFilled);

        foreach ($files as $k => $file) {
            $voucher = $this->notificationCenter->getBulkyGoodsStorage()->store(
                FileItem::fromPath($file['tmp_name'], $file['name'], $file['type'], $file['size'])
            );

            $tokens['form_'.$k] = $voucher;
            $bulkyItemVouchers[] = $voucher;
        }

        // Make sure we don't pass any objects as tokens
        $tokens = array_filter($tokens, static fn ($v) => !\is_object($v));

        $stamps = $this->notificationCenter->createTokenAndLocaleStampsForNotification(
            (int) $formData['nc_notification'],
            $tokens
        );

        if (0 !== \count($bulkyItemVouchers)) {
            $stamps = $stamps->with(new BulkyItemsStamp($bulkyItemVouchers));
        }

        $this->notificationCenter->sendNotificationWithStamps((int) $formData['nc_notification'], $stamps);
    }
}
