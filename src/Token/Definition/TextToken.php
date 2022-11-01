<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

class TextToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'text';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }
}
