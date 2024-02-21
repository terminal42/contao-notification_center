<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class MemberActivationNotificationType implements NotificationTypeInterface
{
    public const NAME = 'member_activation';

    public function __construct(private TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(TextTokenDefinition::class, 'domain', 'member_activation.domain'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_*', 'member_activation.member_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_raw_*', 'member_activation.member_raw_*'),
        ];
    }
}
