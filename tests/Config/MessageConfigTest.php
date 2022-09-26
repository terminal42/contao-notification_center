<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Config;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;

class MessageConfigTest extends TestCase
{
    public function testInteraction(): void
    {
        $config = MessageConfig::fromArray([
            'foobar' => 'test',
        ]);

        $config = $config->withParameter('stdClass', new \stdClass());

        $this->assertInstanceOf(\stdClass::class, $config->getObject('stdClass', \stdClass::class));
        $this->assertNull($config->getObject('stdClass', NotificationConfig::class));
    }
}
