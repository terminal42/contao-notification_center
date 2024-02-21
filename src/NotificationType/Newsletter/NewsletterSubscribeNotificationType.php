<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType\Newsletter;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class NewsletterSubscribeNotificationType implements NotificationTypeInterface
{
    public const NAME = 'newsletter_subscribe';

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
            $this->factory->create(EmailTokenDefinition::class, 'recipient_email', 'newsletter.recipient_email'),
            $this->factory->create(TextTokenDefinition::class, 'link', 'newsletter.link'),
            $this->factory->create(TextTokenDefinition::class, 'token', 'newsletter.token'),
            $this->factory->create(TextTokenDefinition::class, 'channels', 'newsletter.channels'),
            $this->factory->create(TextTokenDefinition::class, 'channel_ids', 'newsletter.channel_ids'),
        ];
    }
}
