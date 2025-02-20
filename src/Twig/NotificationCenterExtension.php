<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Contao\CoreBundle\Twig\Runtime\FormatterRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NotificationCenterExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_center_file_url', [NotificationCenterRuntime::class, 'fileUrl']),
        ];
    }

    public function getFilters(): array
    {
        // This class only exists in Contao 5 and provides the format_bytes filter.
        if (class_exists(FormatterRuntime::class)) {
            return [];
        }

        return [
            new TwigFilter(
                'format_bytes',
                [NotificationCenterRuntime::class, 'formatBytes'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
