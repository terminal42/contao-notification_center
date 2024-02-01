<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class MemberRegistrationNotificationType implements NotificationTypeInterface
{
    public const NAME = 'member_registration';

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
            $this->factory->create(TextToken::DEFINITION_NAME, 'domain', 'member_activation.domain'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'activation', 'member_activation.activation'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'link', 'member_activation.link'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_*', 'member_activation.member_*'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_raw_*', 'member_activation.member_raw_*'),
        ];
    }
}
