<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Twig\Extension\RuntimeExtensionInterface;

final class NotificationCenterRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly BulkyItemStorage $bulkyItemStorage,
    ) {
    }

    public function fileUrl(string $voucher, int|null $ttl = null): string
    {
        return $this->bulkyItemStorage->generatePublicUri($voucher, $ttl);
    }

    /**
     * Convert a byte value into a human-readable format.
     */
    public function formatBytes(int $bytes, int $decimals = 1): string
    {
        $this->framework->initialize();

        return System::getReadableSize($bytes, $decimals);
    }
}
