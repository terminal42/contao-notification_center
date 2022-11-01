<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token\Definition;

class HtmlToken extends AbstractTokenDefinition
{
    public const DEFINITION_NAME = 'html';

    public function getDefinitionName(): string
    {
        return self::DEFINITION_NAME;
    }
}
