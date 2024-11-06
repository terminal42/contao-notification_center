<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\BulkyItem;

use Contao\CoreBundle\Filesystem\ExtraMetadata;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\FilesystemItemIterator;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\BulkyItem\FileItem;

class BulkItemStorageTest extends TestCase
{
    public function testValidVoucherFormat(): void
    {
        $this->assertTrue(BulkyItemStorage::validateVoucherFormat('20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde'));
        $this->assertFalse(BulkyItemStorage::validateVoucherFormat('20221228a10aed4d-abe1-498f-adfc-b2e54fbbcbde'));
        $this->assertFalse(BulkyItemStorage::validateVoucherFormat('a10aed4d-abe1-498f-adfc-b2e54fbbcbde'));
        $this->assertFalse(BulkyItemStorage::validateVoucherFormat('20221228/foobar'));
    }

    public function testStore(): void
    {
        $vfs = $this->createMock(VirtualFilesystemInterface::class);
        $vfs
            ->expects($this->once())
            ->method('writeStream')
            ->with(
                $this->callback(
                    function (string $voucher) {
                        $this->assertTrue(BulkyItemStorage::validateVoucherFormat($voucher));

                        return true;
                    },
                ),
                $this->callback(
                    function ($contents) {
                        $this->assertSame('i-am-a-png', stream_get_contents($contents));

                        return true;
                    },
                ),
            )
        ;
        $vfs
            ->expects($this->once())
            ->method('setExtraMetadata')
            ->with(
                $this->callback(
                    function (string $voucher) {
                        $this->assertTrue(BulkyItemStorage::validateVoucherFormat($voucher));

                        return true;
                    },
                ),
                $this->callback(
                    function (ExtraMetadata $meta) {
                        $this->assertArrayHasKey('storage_meta', $meta);
                        $this->assertSame([
                            'name' => 'foobar.png',
                            'type' => 'image/png',
                            'size' => 100,
                        ], $meta->get('storage_meta')['item']);
                        $this->assertSame(FileItem::class, $meta->get('storage_meta')['class']);

                        return true;
                    },
                ),
            )
        ;

        $storage = new BulkyItemStorage($vfs);
        $voucher = $storage->store($this->createFileItem());

        $this->assertTrue(BulkyItemStorage::validateVoucherFormat($voucher));
    }

    public function testHas(): void
    {
        $vfs = $this->createMock(VirtualFilesystemInterface::class);
        $vfs
            ->expects($this->once())
            ->method('has')
            ->with('a10aed4d-abe1-498f-adfc-b2e54fbbcbde')
            ->willReturn(true)
        ;

        $storage = new BulkyItemStorage($vfs);
        $this->assertTrue($storage->has('a10aed4d-abe1-498f-adfc-b2e54fbbcbde'));
    }

    public function testRetrieve(): void
    {
        $vfs = $this->createMock(VirtualFilesystemInterface::class);
        $vfs
            ->expects($this->once())
            ->method('get')
            ->with('a10aed4d-abe1-498f-adfc-b2e54fbbcbde')
            ->willReturn(new FilesystemItem(true, 'foobar', null, null, null, new ExtraMetadata([
                'storage_meta' => [
                    'item' => [
                        'name' => 'foobar.png',
                        'type' => 'image/png',
                        'size' => 100,
                    ],
                    'class' => FileItem::class,
                ],
            ])))
        ;

        $vfs
            ->expects($this->once())
            ->method('readStream')
            ->with('a10aed4d-abe1-498f-adfc-b2e54fbbcbde')
            ->willReturn($this->createStream())
        ;

        $storage = new BulkyItemStorage($vfs);
        $item = $storage->retrieve('a10aed4d-abe1-498f-adfc-b2e54fbbcbde');

        $this->assertInstanceOf(FileItem::class, $item);
        $this->assertSame('i-am-a-png', stream_get_contents($item->getContents()));
        $this->assertSame(
            [
                'name' => 'foobar.png',
                'type' => 'image/png',
                'size' => 100,
            ],
            $item->getMeta(),
        );
    }

    public function testPrune(): void
    {
        $vfs = $this->createMock(VirtualFilesystemInterface::class);
        $vfs
            ->expects($this->once())
            ->method('listContents')
            ->with('')
            ->willReturn(
                new FilesystemItemIterator([
                    new FilesystemItem(false, '20220101'),
                    new FilesystemItem(false, date('Ymd')),
                ]),
            )
        ;

        $vfs
            ->expects($this->once())
            ->method('deleteDirectory')
            ->with('20220101')
        ;

        $storage = new BulkyItemStorage($vfs);
        $storage->prune();
    }

    private function createFileItem(): FileItem
    {
        return FileItem::fromStream($this->createStream(), 'foobar.png', 'image/png', 100);
    }

    /**
     * @return resource
     */
    private function createStream()
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, 'i-am-a-png');
        rewind($stream);

        return $stream;
    }
}
