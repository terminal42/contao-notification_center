<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel\Stamp;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\AsynchronousDeliveryStamp;

final class AsynchronousDeliveryStampTest extends TestCase
{
    public function testConstructorWithValidIdentifier(): void
    {
        $stamp = new AsynchronousDeliveryStamp('valid_identifier');
        $this->assertSame('valid_identifier', $stamp->identifier);
    }

    public function testConstructorWithInvalidIdentifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier length must be between 1 and 64 characters.');

        $longIdentifier = str_repeat('a', 65); // 65 characters
        new AsynchronousDeliveryStamp($longIdentifier);
    }

    public function testToArray(): void
    {
        $stamp = new AsynchronousDeliveryStamp('test_id');
        $this->assertSame(['identifier' => 'test_id'], $stamp->toArray());
    }

    public function testFromArray(): void
    {
        $data = ['identifier' => 'array_id'];
        $stamp = AsynchronousDeliveryStamp::fromArray($data);
        $this->assertSame('array_id', $stamp->identifier);
    }

    public function testCreateWithRandomId(): void
    {
        $stamp = AsynchronousDeliveryStamp::createWithRandomId();
        $this->assertSame(64, \strlen($stamp->identifier));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $stamp->identifier);
    }
}
