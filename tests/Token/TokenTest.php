<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;
use Terminal42\NotificationCenterBundle\Token\Token;

class TokenTest extends TestCase
{
    /**
     * @dataProvider fromMixedValueProvider
     */
    public function testFromMixedValue(mixed $value, string $expectedValue): void
    {
        $token = Token::fromMixedValue(new WildcardToken('form_*', 'form'), 'form_foobar', $value);
        $this->assertSame($expectedValue, (string) $token->getValue());
    }

    public function fromMixedValueProvider(): \Generator
    {
        yield 'String token' => [
            'foobar',
            'foobar',
        ];

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
