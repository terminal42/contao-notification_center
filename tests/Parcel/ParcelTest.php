<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LocaleStamp;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;

class ParcelTest extends TestCase
{
    public function testBasics(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $parcel = $parcel->withStamp(new LocaleStamp('de_CH'));

        $this->assertSame('bar', $parcel->getMessageConfig()->get('foo'));
        $this->assertTrue($parcel->hasStamp(LocaleStamp::class));
        $this->assertTrue($parcel->hasStamps([LocaleStamp::class]));

        $this->assertFalse($parcel->isSealed());
    }

    public function testCannotAddStampToSealedParcel(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot add a stamp to a sealed collection.');

        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $sealed = $parcel->seal();

        $sealed->withStamp(new LocaleStamp('de_CH'));
    }

    public function testCannotDuplicateAnUnsealedParcel(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Why duplicating a parcel if the current one is not sealed yet?');

        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $parcel->duplicate();
    }

    public function testDuplicatingASealedParcel(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $parcel = $parcel->withStamp(new LocaleStamp('de_CH'));
        $sealed = $parcel->seal();
        $this->assertTrue($sealed->isSealed());
        $this->assertTrue($sealed->getStamps()->isSealed());

        $duplicate = $sealed->duplicate();

        $this->assertFalse($duplicate->isSealed());
        $this->assertFalse($duplicate->getStamps()->isSealed());
        $this->assertSame(['foo' => 'bar'], $duplicate->getMessageConfig()->all());
        $this->assertTrue($duplicate->getStamps()->has(LocaleStamp::class));
    }

    public function testSerialize(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $parcel = $parcel->withStamp(new LocaleStamp('de_CH'));
        $sealed = $parcel->seal();

        $serialized = $sealed->serialize();
        $parcel = Parcel::fromSerialized($serialized);

        $this->assertTrue($parcel->hasStamp(LocaleStamp::class));
        $this->assertTrue($parcel->isSealed());
    }
}
