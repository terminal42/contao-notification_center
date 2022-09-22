<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

interface MessageTypeInterface
{
    public function getName(): string;

    /**
     * @return array<TokenDefinitionInterface>
     */
    public function getTokenDefinitions(): array;
}
