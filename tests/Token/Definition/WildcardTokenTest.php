<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token\Definition;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;

class WildcardTokenTest extends TestCase
{
    public function testBasicTokenInteraction(): void
    {
        $token = new WildcardToken('foobar_*', 'translation_key');

        $this->assertTrue($token->matchesTokenName('foobar_whatever'));
        $this->assertFalse($token->matchesTokenName('other_token'));
    }
}
