<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Config;

class LanguageConfig extends AbstractConfig
{
    public function getMessage(): int
    {
        return $this->getInt('pid');
    }
}
