<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class MemberPersonalDataMessageType implements MessageTypeInterface
{
    public const NAME = 'member_personaldata';

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
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_*', 'member_personal_data.member_*'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'member_old_*', 'member_personal_data.member_old_*'),
            $this->factory->create(WildcardToken::DEFINITION_NAME, 'changed_*', 'member_personal_data.changed_*'),
            $this->factory->create(TextToken::DEFINITION_NAME, 'comparison_text', 'member_personal_data.comparison_text'),
            $this->factory->create(HtmlToken::DEFINITION_NAME, 'comparison_html', 'member_personal_data.comparison_html'),
        ];
    }
}
