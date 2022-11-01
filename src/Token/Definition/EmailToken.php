<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

class EmailToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'email';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }
}
