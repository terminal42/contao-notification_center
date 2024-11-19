<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationCenterExtension extends AbstractExtension
{
    public function __construct(private readonly BulkyItemStorage $bulkyItemStorage)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_center_file_url', [$this, 'fileUrl']),
        ];
    }

    public function fileUrl(string $voucher, int|null $ttl = null): string
    {
        return $this->bulkyItemStorage->generatePublicUri($voucher, $ttl);
    }
}
