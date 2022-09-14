<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class Terminal42NotificationCenterBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
