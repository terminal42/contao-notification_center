<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Config;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;

class MessageConfigTest extends TestCase
{
    public function testInteraction(): void
    {
        $config = MessageConfig::fromArray([
            'foobar' => 'test',
        ]);

        $config = $config->withParameter('foobar2', ['nested' => true]);

        $this->assertSame([
            'foobar' => 'test',
            'foobar2' => ['nested' => true],
        ], $config->all());
        $this->assertNull($config->get('foo'));
    }
}
