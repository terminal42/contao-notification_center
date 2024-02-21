<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class LostPasswordNotificationType implements NotificationTypeInterface
{
    public const NAME = 'member_password';

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
            $this->factory->create(EmailTokenDefinition::class, 'recipient_email', 'member_password.recipient_email'),
            $this->factory->create(TextTokenDefinition::class, 'domain', 'member_password.domain'),
            $this->factory->create(TextTokenDefinition::class, 'link', 'member_password.link'),
            $this->factory->create(TextTokenDefinition::class, 'token', 'member_password.token'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_*', 'member_password.member_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_raw_*', 'member_password.member_raw_*'),
        ];
    }
}
