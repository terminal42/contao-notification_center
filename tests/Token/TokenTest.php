<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Token;

class TokenTest extends TestCase
{
    #[DataProvider('anythingProvider')]
    public function testFromAnything(mixed $value, string $expectedParserValue): void
    {
        $token = Token::fromValue('token', $value);
        $this->assertSame($value, $token->getValue());
        $this->assertSame($expectedParserValue, $token->getParserValue());
    }

    /**
     * @param array<mixed> $value
     */
    #[DataProvider('arrayProvider')]
    public function testArrayParserFormat(array $value, string $expectedParserValue): void
    {
        $token = Token::fromValue('form_foobar', $value);
        $this->assertSame($expectedParserValue, $token->getParserValue());
    }

    public static function arrayProvider(): \Generator
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

    public static function anythingProvider(): \Generator
    {
        yield [
            'foobar',
            'foobar',
        ];

        yield [
            42,
            '42',
        ];

        yield [
            42.00,
            '42',
        ];

        yield [
            fopen('php://memory', 'r'),
            '',
        ];

        yield [
            new class() {
                public function __toString(): string
                {
                    return 'test';
                }
            },
            'test',
        ];

        yield [
            new class() {},
            '',
        ];
    }
}
