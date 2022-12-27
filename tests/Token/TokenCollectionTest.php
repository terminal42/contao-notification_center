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
        $tokenCollection->add(new ArrayToken(['blue', 'orange'], 'form_other_color', TextToken::DEFINITION_NAME));

        $this->assertSame([
            'form_color' => 'blue',
            'form_other_color' => 'blue, orange',
        ], $tokenCollection->forSimpleTokenParser());

        // Test if serialization and unserialization works
        $serialized = $tokenCollection->serialize();
        $tokenCollection = TokenCollection::fromSerialized($serialized);

        $this->assertSame([
            'form_color' => 'blue',
            'form_other_color' => 'blue, orange',
        ], $tokenCollection->forSimpleTokenParser());
    }
}
