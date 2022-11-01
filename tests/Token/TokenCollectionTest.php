<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Definition\WildcardToken;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class TokenCollectionTest extends TestCase
{
    /**
     * @param array<string, mixed>  $input
     * @param array<string, string> $expectedRawKeyValue
     *
     * @dataProvider fromRawProvider
     */
    public function testFromRaw(array $input, array $expectedRawKeyValue): void
    {
        $tokenCollection = TokenCollection::fromRawAndDefinitions($input, [new WildcardToken('form_*', 'form')]);

        $this->assertSame($expectedRawKeyValue, $tokenCollection->asKeyValue(true));
    }

    public function fromRawProvider(): \Generator
    {
        yield 'Regular nested arrays' => [
            [
                'form_firstname' => 'First name',
                'form_color' => [
                    'Red',
                    'Green',
                ],
            ],
            [
                'form_firstname' => 'First name',
                'form_color' => 'Red, Green',
            ],
        ];
    }
}
