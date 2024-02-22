<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\Token;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class TokenCollectionTest extends TestCase
{
    public function testCollectionHandling(): void
    {
        $tokenCollection = new TokenCollection();
        $tokenCollection->add(Token::fromValue('form_color', 'blue'));
        $tokenCollection->addToken(Token::fromValue('form_product', 't-shirt'));
        $tokenCollection->add(Token::fromValue('form_other_color', ['blue', 'orange']));

        $this->assertTrue($tokenCollection->has('form_color'));
        $this->assertFalse($tokenCollection->has('form_i_do_not_exist'));

        $this->assertSame(
            [
                'form_color' => 'blue',
                'form_product' => 't-shirt',
                'form_other_color' => 'blue, orange',
            ],
            $tokenCollection->forSimpleTokenParser(),
        );

        $this->assertSame(
            [
                'form_color' => 'blue',
                'form_product' => 't-shirt',
                'form_other_color' => [
                    'blue',
                    'orange',
                ],
            ],
            $tokenCollection->toKeyValue(),
        );

        $array = $tokenCollection->toSerializableArray();
        $tokenCollection = TokenCollection::fromSerializedArray($array);

        $this->assertSame(
            [
                'form_color' => 'blue',
                'form_product' => 't-shirt',
                'form_other_color' => 'blue, orange',
            ],
            $tokenCollection->forSimpleTokenParser(),
        );

        $this->assertSame('blue', $tokenCollection->getByName('form_color')->getParserValue());
        $this->assertNull($tokenCollection->getByName('form_i_do_not_exist'));
    }

    public function testMerge(): void
    {
        $tokenCollectionA = new TokenCollection();
        $tokenCollectionA->add(Token::fromValue('form_color', 'blue'));
        $tokenCollectionA->add(Token::fromValue('form_product', 't-shirt'));

        $tokenCollectionB = new TokenCollection();
        $tokenCollectionB->add(Token::fromValue('form_color', 'green'));

        /** @var TokenCollection $merged */
        $merged = $tokenCollectionA->merge($tokenCollectionB);

        $this->assertSame(
            [
                'form_color' => 'green',
                'form_product' => 't-shirt',
            ],
            $merged->forSimpleTokenParser(),
        );
    }
}
