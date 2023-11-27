<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\ArrayToken;

class ArrayTokenTest extends TestCase
{
    /**
     * @dataProvider arrayProvider
     */
    public function testFromMixedValue(array $value, string $expectedParserValue): void
    {
        $token = new ArrayToken($value, 'form_foobar');
        $this->assertSame($expectedParserValue, $token->getParserValue());
    }

    public function arrayProvider(): \Generator
    {
        yield 'Simple list array token' => [
            [
                'red',
                'green',
                'blue',
            ],
            'red, green, blue',
        ];

        yield 'Simple key value token' => [
            [
                'from' => 'May',
                'to' => 'June',
            ],
            'from: May, to: June',
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
            '0: red, 1: green, blue: blue [{"0":"orange","magenta":["cyan"]}]',
        ];
    }
}
