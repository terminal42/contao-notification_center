<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LocaleStamp;
use Terminal42\NotificationCenterBundle\Parcel\StampCollection;

class ParcelTest extends TestCase
{
    public function testMessageConfigAndSealed(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']));

        $this->assertSame('bar', $parcel->getMessageConfig()->get('foo'));

        $this->assertFalse($parcel->isSealed());
        $parcel = $parcel->seal();
        $this->assertTrue($parcel->isSealed());
    }

    public function testAddingStampsBeforeAndAfterSealing(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']));
        $parcel = $parcel->withStamp(new LocaleStamp('de_CH'));
        $sealed = $parcel->seal();

        $parcel = $parcel->withStamp(new BulkyItemsStamp(['20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde']));
        $sealed = $sealed->withStamp(new BulkyItemsStamp(['20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde']));

        // Both should have both stamps
        $this->assertTrue($parcel->hasStamp(LocaleStamp::class));
        $this->assertTrue($parcel->hasStamp(BulkyItemsStamp::class));
        $this->assertTrue($parcel->hasStamps([LocaleStamp::class, BulkyItemsStamp::class]));
        $this->assertTrue($sealed->hasStamp(LocaleStamp::class));
        $this->assertTrue($sealed->hasStamp(BulkyItemsStamp::class));
        $this->assertTrue($sealed->hasStamps([LocaleStamp::class, BulkyItemsStamp::class]));

        // If one adds an already present stamp, the stamp after sealing must always win
        $sealed = $sealed->withStamp(new LocaleStamp('de_DE'));
        $this->assertSame('de_DE', $sealed->getStamp(LocaleStamp::class)->locale);

        // The unsealed one should not have the BulkyItemsStamp now but the LocaleStamp should still be here
        $unsealed = $sealed->unseal();
        $this->assertFalse($unsealed->hasStamp(BulkyItemsStamp::class));
        $this->assertTrue($unsealed->hasStamp(LocaleStamp::class));
    }

    public function testSerialize(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray(['foo' => 'bar']), new StampCollection());
        $parcel = $parcel->withStamp(new LocaleStamp('de_CH'));
        $sealed = $parcel->seal();
        $sealed = $sealed->withStamp(new BulkyItemsStamp(['20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde']));

        $serialized = $sealed->serialize();
        $parcel = Parcel::fromSerialized($serialized);

        $this->assertTrue($parcel->hasStamp(LocaleStamp::class));
        $this->assertTrue($parcel->hasStamp(BulkyItemsStamp::class));
        $this->assertTrue($parcel->isSealed());
    }
}
