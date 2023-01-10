<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Token;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Token\ArrayToken;
use Terminal42\NotificationCenterBundle\Token\Definition\TextToken;
use Terminal42\NotificationCenterBundle\Token\StringToken;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class TokenCollectionTest extends TestCase
{
    public function testCollectionHandling(): void
    {
        $tokenCollection = new TokenCollection();
        $tokenCollection->add(new StringToken('blue', 'form_color', TextToken::DEFINITION_NAME));
        $tokenCollection->add(new StringToken('t-shirt', 'form_product', TextToken::DEFINITION_NAME));
        $tokenCollection->add(new ArrayToken(['blue', 'orange'], 'form_other_color', TextToken::DEFINITION_NAME));

        $this->assertSame([
            'form_color' => 'blue',
            'form_product' => 't-shirt',
            'form_other_color' => 'blue, orange',
        ], $tokenCollection->forSimpleTokenParser());

        $array = $tokenCollection->toArray();
        $tokenCollection = TokenCollection::fromArray($array);

        $this->assertSame([
            'form_color' => 'blue',
            'form_product' => 't-shirt',
            'form_other_color' => 'blue, orange',
        ], $tokenCollection->forSimpleTokenParser());

        $this->assertSame('blue', $tokenCollection->getByName('form_color')->getParserValue());
        $this->assertNull($tokenCollection->getByName('form_i_do_not_exist'));
    }
}
