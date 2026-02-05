<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Util;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Util\Json;

final class JsonTest extends TestCase
{
    private const ARRAY_WITH_BINARY_DATA = [
        'foo' => 'bar',
        'other' => 42,
        'binary' => "b\xE4r",
        'nested' => [
            'foo' => 'bar',
            'binary' => "b\xE4r",
            'nested' => [
                'foo' => 'bar',
                'binary' => "b\xE4r",
            ],
        ],
    ];

    private const ENCODED = '{"foo":"bar","other":42,"binary":"base64:\/\/YuRy","nested":{"foo":"bar","binary":"base64:\/\/YuRy","nested":{"foo":"bar","binary":"base64:\/\/YuRy"}}}';

    public function testEncodesNonUtf8CharactersCorrectly(): void
    {
        $this->assertSame(self::ENCODED, Json::utf8SafeEncode(self::ARRAY_WITH_BINARY_DATA));
    }

    public function testDecodesNonUtf8CharactersCorrectly(): void
    {
        $this->assertSame(self::ARRAY_WITH_BINARY_DATA, Json::utf8SafeDecode(self::ENCODED));
    }
}
