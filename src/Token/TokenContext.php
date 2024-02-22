<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Token;

use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\FileTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

enum TokenContext: string
{
    case Email = 'email';
    case Text = 'text';
    case Html = 'html';
    case File = 'file';

    public function definitions(): array
    {
        return match ($this) {
            TokenContext::Email => [
                AnythingTokenDefinition::class,
                EmailTokenDefinition::class,
            ],
            TokenContext::Text => [
                AnythingTokenDefinition::class,
                EmailTokenDefinition::class,
                TextTokenDefinition::class,
                FileTokenDefinition::class,
            ],
            TokenContext::Html => [
                AnythingTokenDefinition::class,
                EmailTokenDefinition::class,
                TextTokenDefinition::class,
                FileTokenDefinition::class,
                HtmlTokenDefinition::class,
            ],
            TokenContext::File => [
                AnythingTokenDefinition::class,
                FileTokenDefinition::class,
            ],
        };
    }
}
