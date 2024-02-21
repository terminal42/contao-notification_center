<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\NotificationType\Newsletter;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

class NewsletterUnsubscribeNotificationType implements NotificationTypeInterface
{
    public const NAME = 'newsletter_unsubscribe';

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
            $this->factory->create(TextTokenDefinition::class, 'channels', 'newsletter.channels'),
            $this->factory->create(TextTokenDefinition::class, 'channel_ids', 'newsletter.channel_ids'),
        ];
    }
}
