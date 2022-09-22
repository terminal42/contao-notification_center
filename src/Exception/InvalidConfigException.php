<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Exception;

use Terminal42\NotificationCenterBundle\Config\AbstractConfig;

class InvalidConfigException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function becauseItDoesNotExtendAbstractConfig(string $className): self
    {
        return new self(sprintf(
            'The provided class name "%s" must implement "%s".',
            $className,
            AbstractConfig::class
        ));
    }
}
