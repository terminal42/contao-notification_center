<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token\Definition;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;

final class TextTokenTest extends TestCase
{
    public function testBasicTokenInteraction(): void
    {
        $token = new TextTokenDefinition('foobar', 'translation_key');

        $this->assertSame('foobar', $token->getTokenName());
        $this->assertSame('translation_key', $token->getTranslationKey());

        $this->assertTrue($token->matches('foobar', 'value'));
        $this->assertFalse($token->matches('other_token', 'value'));
    }

    public function testWildcardInteraction(): void
    {
        $token = new TextTokenDefinition('foobar_*', 'translation_key');

        $this->assertSame('foobar_*', $token->getTokenName());
        $this->assertSame('translation_key', $token->getTranslationKey());

        $this->assertTrue($token->matches('foobar_whatever', 'value'));
        $this->assertFalse($token->matches('other_token', 'value'));
    }
}
