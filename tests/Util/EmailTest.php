<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Util;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Util\Email;

final class EmailTest extends TestCase
{
    public function testSplitEmailAddressesWithoutFriendlyName(): void
    {
        $input = 'john@example.com, jane@example.com';

        $expected = [
            'john@example.com',
            'jane@example.com',
        ];

        $this->assertSame($expected, Email::splitEmailAddresses($input));
    }

    public function testSplitEmailAddressesWithFriendlyNameKeepingFriendly(): void
    {
        $input = 'John Doe <john@example.com>, Jane Doe <jane@example.com>';

        $expected = [
            'John Doe <john@example.com>',
            'Jane Doe <jane@example.com>',
        ];

        $this->assertSame($expected, Email::splitEmailAddresses($input, true));
    }

    public function testSplitEmailAddressesWithFriendlyNameNotKeepingFriendly(): void
    {
        $input = 'John Doe <john@example.com>, Jane Doe <jane@example.com>';

        $expected = [
            'john@example.com',
            'jane@example.com',
        ];

        $this->assertSame($expected, Email::splitEmailAddresses($input));
    }

    public function testSplitEmailAddressesSkipsInvalidEmails(): void
    {
        $input = 'not-an-email, valid@example.com';

        $expected = [
            'valid@example.com',
        ];

        $this->assertSame($expected, Email::splitEmailAddresses($input));
    }

    public function testSplitEmailAddressesEmptyInput(): void
    {
        $this->assertSame([], Email::splitEmailAddresses(''));
    }
}
