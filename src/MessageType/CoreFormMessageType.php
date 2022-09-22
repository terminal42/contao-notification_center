<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\MessageType;

use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class CoreFormMessageType implements MessageTypeInterface
{
    public const NAME = 'core_form';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            EmailToken::createWithTranslationKeyPrefix('admin_email', ''), // TODO: Generalize?
            WildcardToken::createWithTranslationKeyPrefix('form_*', 'form.'),
            WildcardToken::createWithTranslationKeyPrefix('formconfig_*', 'form.'),
            WildcardToken::createWithTranslationKeyPrefix('formlabel_*', 'form.'),
            TextToken::createWithTranslationKeyPrefix('raw_data', 'form.'),
            TextToken::createWithTranslationKeyPrefix('raw_data_filled', 'form.'),
        ];
    }
}
