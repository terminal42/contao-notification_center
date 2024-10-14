<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\BulkyItem;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\MimeTypes;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItemFactory;
use Terminal42\NotificationCenterBundle\Test\VirtualFilesystemTestTrait;

class FileItemFactoryTest extends TestCase
{
    use VirtualFilesystemTestTrait;

    public function testCreateFromLocalPath(): void
    {
        $factory = new FileItemFactory(new MimeTypes());
        $item = $factory->createFromLocalPath(__DIR__.'/../Fixtures/name.jpg');
        $this->assertSame('name.jpg', $item->getName());
        $this->assertSame('image/jpeg', $item->getMimeType());
        $this->assertSame(333, $item->getSize());
        $this->assertIsResource($item->getContents());
    }

    public function testCreateFromVfsFilesystemItem(): void
    {
        $vfsCollection = $this->createVfsCollection();
        $vfs = $vfsCollection->get('files');
        $vfs->write('media/name.jpg', file_get_contents(__DIR__.'/../Fixtures/name.jpg'));

        $item = $vfs->get('media/name.jpg');

        $factory = new FileItemFactory(new MimeTypes());
        $item = $factory->createFromVfsFilesystemItem($item, $vfs);
        $this->assertSame('name.jpg', $item->getName());
        $this->assertSame('image/jpeg', $item->getMimeType());
        $this->assertSame(333, $item->getSize());
        $this->assertIsResource($item->getContents());
    }
}
