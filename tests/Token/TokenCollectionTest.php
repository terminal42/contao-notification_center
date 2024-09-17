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
        $this->assertTrue($tokenCollection->hasAll('form_color', 'form_product', 'form_other_color'));
        $this->assertFalse($tokenCollection->hasAll('form_color', 'form_product', 'form_i_do_not_exist'));
        $this->assertTrue($tokenCollection->hasAny('form_color', 'form_i_do_not_exist'));
        $this->assertFalse($tokenCollection->hasAny('form_i_do_not_exist'));

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

    public function testInvalidParserTokenNamesAreNormalized(): void
    {
        $tokenCollection = new TokenCollection();
        $tokenCollection->add(Token::fromValue('invalid-because-of-dashes', 'foobar'));
        $tokenCollection->add(Token::fromValue('123invalid_because_of_numbers_at_the_start', 'foobar'));
        $tokenCollection->add(Token::fromValue('1234-', 'foobar'));

        $this->assertSame(
            [
                'invalid_because_of_dashes' => 'foobar',
                '___invalid_because_of_numbers_at_the_start' => 'foobar',
                '_____' => 'foobar',
            ],
            $tokenCollection->forSimpleTokenParser(),
        );

        // Here, they should remain untouched. They are only invalid for the
        // SimpleTokenParser, but they might be very well valid inside a JSON-API gateway
        // or what not.
        $this->assertSame(
            [
                'invalid-because-of-dashes' => 'foobar',
                '123invalid_because_of_numbers_at_the_start' => 'foobar',
                '1234-' => 'foobar',
            ],
            $tokenCollection->toKeyValue(),
        );
    }
}
