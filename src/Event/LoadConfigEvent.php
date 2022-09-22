<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Config\AbstractConfig;

class LoadConfigEvent extends Event
{
    public function __construct(public AbstractConfig $config)
    {
    }
}
