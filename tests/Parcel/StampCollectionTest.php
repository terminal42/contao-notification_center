<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LocaleStamp;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;

class StampCollectionTest extends TestCase
{
    public function testBasics(): void
    {
        $collection = new StampCollection();
        $collection1 = $collection->with(new LocaleStamp('de_CH'));
        $this->assertFalse($collection->has(LocaleStamp::class));
        $this->assertTrue($collection1->has(LocaleStamp::class));

        $sealed = $collection->seal();

        $this->assertFalse($collection->isSealed());
        $this->assertTrue($sealed->isSealed());
    }

    public function testCannotAddStampToSealedCollection(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Cannot add a stamp to a sealed collection.');

        $collection = new StampCollection();
        $sealed = $collection->seal();

        $sealed->with(new LocaleStamp('de_CH'));
    }

    public function testSerialize(): void
    {
        $collection = new StampCollection();
        $collection = $collection->with(new LocaleStamp('de_CH'));
        $collection = $collection->seal();

        $serialized = $collection->serialize();
        $collection = StampCollection::fromSerialized($serialized);

        $this->assertTrue($collection->has(LocaleStamp::class));
        $this->assertTrue($collection->isSealed());
    }
}
