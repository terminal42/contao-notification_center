<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

use Terminal42\NotificationCenterBundle\Util\ParameterBag;

abstract class AbstractConfig extends ParameterBag
{
    public function getId(): int
    {
        return $this->getInt('id');
    }
}
