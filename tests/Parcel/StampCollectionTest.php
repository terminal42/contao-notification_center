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
    }

    public function testSerialize(): void
    {
        $collection = new StampCollection();
        $collection = $collection->with(new LocaleStamp('de_CH'));

        $array = $collection->toArray();
        $collection = StampCollection::fromArray($array);

        $this->assertTrue($collection->has(LocaleStamp::class));
    }
}
