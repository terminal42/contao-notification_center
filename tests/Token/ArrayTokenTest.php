<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\ArrayToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;

class ArrayTokenTest extends TestCase
{
    /**
     * @dataProvider arrayProvider
     */
    public function testFromMixedValue(array $value, string $expectedParserValue): void
    {
        $token = new ArrayToken($value, 'form_foobar', TextToken::DEFINITION_NAME);
        $this->assertSame($expectedParserValue, $token->getParserValue());
    }

    public function arrayProvider(): \Generator
    {
        yield 'Simple array token' => [
            [
                'red',
                'green',
                'blue',
            ],
            'red, green, blue',
        ];

        yield 'Nested array token' => [
            [
                'red',
                'green',
                'blue' => [
                    'orange',
                    'magenta' => [
                        'cyan',
                    ],
                ],
            ],
            'red, green, blue [{"0":"orange","magenta":["cyan"]}]',
        ];
    }
}
