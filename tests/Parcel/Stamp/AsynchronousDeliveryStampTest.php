<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Parcel\Stamp;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\AsynchronousDeliveryStamp;

class AsynchronousDeliveryStampTest extends TestCase
{
    public function testConstructorWithValidIdentifier(): void
    {
        $stamp = new AsynchronousDeliveryStamp('valid_identifier');
        $this->assertInstanceOf(AsynchronousDeliveryStamp::class, $stamp);
        $this->assertSame('valid_identifier', $stamp->identifier);
    }

    public function testConstructorWithInvalidIdentifier(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The identifier length must not exceed 64 characters.');

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
        $this->assertInstanceOf(AsynchronousDeliveryStamp::class, $stamp);
        $this->assertSame('array_id', $stamp->identifier);
    }

    public function testCreateWithRandomId(): void
    {
        $stamp = AsynchronousDeliveryStamp::createWithRandomId();
        $this->assertInstanceOf(AsynchronousDeliveryStamp::class, $stamp);
        $this->assertSame(64, \strlen($stamp->identifier));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $stamp->identifier);
    }
}
