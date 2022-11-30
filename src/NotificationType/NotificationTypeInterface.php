<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

interface NotificationTypeInterface
{
    public function getName(): string;

    /**
     * @return array<TokenDefinitionInterface>
     */
    public function getTokenDefinitions(): array;
}
