<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationCenterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_center_file_url', [NotificationCenterRuntime::class, 'fileUrl']),
        ];
    }
}
