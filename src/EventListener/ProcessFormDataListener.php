<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Codefog\HasteBundle\FileUploadNormalizer;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Form;
use Contao\StringUtil;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\FormConfigStamp;

#[AsHook('processFormData')]
class ProcessFormDataListener
{
    public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly FileUploadNormalizer $fileUploadNormalizer,
        private readonly ConfigLoader $configLoader,
    ) {
    }

    /**
     * @param array<string, mixed>      $submittedData
     * @param array<string, mixed>      $formData
     * @param array<string, mixed>|null $files
     * @param array<string, mixed>      $labels
     */
    public function __invoke(array $submittedData, array $formData, array|null $files, array $labels, Form $form): void
    {
        if (!isset($formData['nc_notification']) || !is_numeric($formData['nc_notification']) || $formData['nc_notification'] <= 0) {
            return;
        }

        $tokens = [];
        $rawData = [];
        $rawDataFilled = [];
        $bulkyItemVouchers = [];
        $files = !\is_array($files) ? [] : $files; // In Contao 4.13, $files can be null

        foreach ($submittedData as $k => $v) {
            // Skip the tokens that are not implodeable
            if (\is_array($v)) {
                foreach ($v as $vv) {
                    if (!\is_scalar($vv)) {
                        continue 2;
                    }
                }
            }

            $label = isset($labels[$k]) && \is_string($labels[$k]) ? StringUtil::decodeEntities($labels[$k]) : ucfirst($k);

            $tokens['formlabel_'.$k] = $label;
            $tokens['form_'.$k] = $v;

            $rawData[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);

            if (\is_array($v) || ('' !== (string) $v)) {
                $rawDataFilled[] = $label.': '.(\is_array($v) ? implode(', ', $v) : $v);
            }
        }

        foreach ($formData as $k => $v) {
            $tokens['formconfig_'.$k] = \is_string($v) ? StringUtil::decodeEntities($v) : $v;
        }

        $tokens['raw_data'] = implode("\n", $rawData);
        $tokens['raw_data_filled'] = implode("\n", $rawDataFilled);
        $tokens['html_data'] = implode("<br>", $rawData);
        $tokens['html_data_filled'] = implode("<br>", $rawDataFilled);

        foreach ($this->fileUploadNormalizer->normalize($files) as $k => $files) {
            $vouchers = [];

            foreach ($files as $file) {
                $fileItem = \is_resource($file['stream']) ?
                    FileItem::fromStream($file['stream'], $file['name'], $file['type'], $file['size']) :
                    FileItem::fromPath($file['tmp_name'], $file['name'], $file['type'], $file['size']);

                $vouchers[] = $this->notificationCenter->getBulkyItemStorage()->store($fileItem);
            }

            $tokens['form_'.$k] = implode(',', $vouchers);
            $bulkyItemVouchers = array_merge($bulkyItemVouchers, $vouchers);
        }

        // Make sure we don't pass any objects as tokens
        $tokens = array_filter($tokens, static fn ($v) => !\is_object($v));

        $stamps = $this->notificationCenter->createBasicStampsForNotification(
            (int) $formData['nc_notification'],
            $tokens,
        );

        if (0 !== \count($bulkyItemVouchers)) {
            $stamps = $stamps->with(new BulkyItemsStamp($bulkyItemVouchers));
        }

        $formConfig = $this->configLoader->loadForm((int) $form->id);

        if (null !== $formConfig) {
            $stamps = $stamps->with(new FormConfigStamp($formConfig));
        }

        $this->notificationCenter->sendNotificationWithStamps((int) $formData['nc_notification'], $stamps);
    }
}
