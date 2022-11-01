<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

class FileToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'file';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }
}
