<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class MemberPersonalDataNotificationType implements NotificationTypeInterface
{
    public const NAME = 'member_personaldata';

    public function __construct(private readonly TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(AnythingTokenDefinition::class, 'member_*', 'member_personal_data.member_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_raw_*', 'member_activation.member_raw_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_old_*', 'member_personal_data.member_old_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'member_old_raw_*', 'member_personal_data.member_old_raw_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'changed_*', 'member_personal_data.changed_*'),
            $this->factory->create(TextTokenDefinition::class, 'comparison_text', 'member_personal_data.comparison_text'),
            $this->factory->create(HtmlTokenDefinition::class, 'comparison_html', 'member_personal_data.comparison_html'),
        ];
    }
}
