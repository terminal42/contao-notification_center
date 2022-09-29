<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class LostPasswordMessageType implements MessageTypeInterface
{
    public const NAME = 'member_password';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            EmailToken::createWithTranslationKeyPrefix('recipient_email', 'member_password.'),
            TextToken::createWithTranslationKeyPrefix('domain', 'member_password.'),
            TextToken::createWithTranslationKeyPrefix('link', 'member_password.'),
            WildcardToken::createWithTranslationKeyPrefix('member_*', 'member_password.'),
        ];
    }
}
