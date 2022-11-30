<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class MemberActivationMessageType implements MessageTypeInterface
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
            $this->factory->create(TextToken::DEFINITION_NAME, 'domain', 'member_activation.domain'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_*', 'member_activation.member_*'),
        ];
    }
}
