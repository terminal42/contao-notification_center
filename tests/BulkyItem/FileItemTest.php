<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\BulkyItem;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;
use Terminal42\NotificationCenterBundle\Exception\BulkyItem\InvalidFileItemException;

class FileItemTest extends TestCase
{
    public function testCannotCreateEmptyNameFileItem(): void
    {
        $this->expectException(InvalidFileItemException::class);
        $this->expectExceptionMessage('Name must not be empty');

        FileItem::fromPath(__DIR__.'/../Fixtures/name.jpg', '', 'image/jpg', 0);
    }

    public function testCannotCreateEmptyMimeTypeFileItem(): void
    {
        $this->expectException(InvalidFileItemException::class);
        $this->expectExceptionMessage('Mime type must not be empty');

        FileItem::fromPath(__DIR__.'/../Fixtures/name.jpg', 'name.jpg', '', 0);
    }

    public function testCannotCreateInvalidFileSizeFileItem(): void
    {
        $this->expectException(InvalidFileItemException::class);
        $this->expectExceptionMessage('File size must not be smaller than 0');

        FileItem::fromPath(__DIR__.'/../Fixtures/name.jpg', 'name.jpg', 'image/jpg', -42);
    }
}
