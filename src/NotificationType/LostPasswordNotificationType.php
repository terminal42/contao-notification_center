<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

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
            $this->factory->create(EmailToken::DEFINITION_NAME, 'recipient_email', 'member_password.recipient_email'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'domain', 'member_password.domain'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'link', 'member_password.link'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'activation', 'member_password.activation'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_*', 'member_password.member_*'),
        ];
    }
}
