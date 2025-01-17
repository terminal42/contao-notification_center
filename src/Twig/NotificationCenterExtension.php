<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class NotificationCenterExtension extends AbstractExtension
{
    public function __construct(private readonly Environment $env)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_center_file_url', [NotificationCenterRuntime::class, 'fileUrl']),
        ];
    }

    public function getFilters(): array
    {
        // Use the format_bytes filter in Contao 5
        if (null !== $this->env->getFilter('format_bytes')) {
            return [];
        }

        return [
            new TwigFilter(
                'format_bytes',
                [NotificationCenterRuntime::class, 'formatBytes'],
                ['is_safe' => ['html']],
            )
        ];
    }
}
