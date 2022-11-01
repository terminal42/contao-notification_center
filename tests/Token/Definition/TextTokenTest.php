<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token\Definition;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;

class TextTokenTest extends TestCase
{
    public function testBasicTokenInteraction(): void
    {
        $token = new TextToken('foobar', 'translation_key');

        $this->assertSame('foobar', $token->getTokenName());
        $this->assertSame('translation_key', $token->getTranslationKey());

        $this->assertTrue($token->matchesTokenName('foobar'));
        $this->assertFalse($token->matchesTokenName('other_token'));
    }
}
