<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

use Terminal42\NotificationCenterBundle\Parcel\Stamp\StampInterface;
use Terminal42\NotificationCenterBundle\Util\ParameterBag;

abstract class AbstractConfig extends ParameterBag implements StampInterface
{
    public function getId(): int
    {
        return $this->getInt('id');
    }
}
