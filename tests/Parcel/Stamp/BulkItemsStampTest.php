<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel\Stamp;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;

class BulkItemsStampTest extends TestCase
{
    public function testStampHandling(): void
    {
        $stamp = new BulkyItemsStamp([
            '20230104/af86bc0e-55fd-4eac-886f-9f90eae9eeda',
            '20230101/f852ddd9-3e35-4ac3-af5e-cb8cb9044510',
        ]);

        $this->assertTrue($stamp->has('20230104/af86bc0e-55fd-4eac-886f-9f90eae9eeda'));
        $this->assertFalse($stamp->has('20221212/2b2562b5-149a-4e0a-b3fa-046080815993'));
        $this->assertSame([
            '20230104/af86bc0e-55fd-4eac-886f-9f90eae9eeda',
            '20230101/f852ddd9-3e35-4ac3-af5e-cb8cb9044510',
        ], $stamp->all());

        $array = $stamp->toArray();

        $this->assertSame([
            '20230104/af86bc0e-55fd-4eac-886f-9f90eae9eeda',
            '20230101/f852ddd9-3e35-4ac3-af5e-cb8cb9044510',
        ], $array);

        $stamp = BulkyItemsStamp::fromArray($array);
        $this->assertSame([
            '20230104/af86bc0e-55fd-4eac-886f-9f90eae9eeda',
            '20230101/f852ddd9-3e35-4ac3-af5e-cb8cb9044510',
        ], $stamp->all());
    }
}
