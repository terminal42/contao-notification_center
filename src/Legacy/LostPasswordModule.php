<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Legacy;

use Contao\ModuleLostPassword; // Contao 4.13
use Contao\ModulePassword; // Contao 5

if (class_exists(ModuleLostPassword::class)) {
    class LostPasswordModule extends ModuleLostPassword
    {
    }
} else {
    class LostPasswordModule extends ModulePassword
    {
    }
}
