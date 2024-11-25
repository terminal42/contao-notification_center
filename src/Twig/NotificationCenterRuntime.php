<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Twig\Extension\RuntimeExtensionInterface;

final class NotificationCenterRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly BulkyItemStorage $bulkyItemStorage)
    {
    }

    public function fileUrl(string $voucher, int|null $ttl = null): string
    {
        return $this->bulkyItemStorage->generatePublicUri($voucher, $ttl);
    }
}
