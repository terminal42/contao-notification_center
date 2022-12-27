<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Symfony\Component\Uid\Uuid;

class BulkyItemStorage
{
    public function __construct(private VirtualFilesystemInterface $filesystem, private int $retentionPeriodInDays = 7)
    {
    }

    /**
     * Returns the voucher with which you can come back to the storage and get your bulky item back.
     * Would probably also call this a "token" in a real world cloakroom but in order to not mess up
     * the terminology with simple tokens etc., we're going for voucher here.
     */
    public function store(BulkyItemInterface $item): string
    {
        $voucher = date('Ymd').'/'.Uuid::v4()->toRfc4122();
        $this->filesystem->writeStream($voucher, $item->getContents());

        $meta = [
            'item' => $item->getMeta(),
            'class' => \get_class($item),
        ];

        $this->filesystem->setExtraMetadata($voucher, ['storage_meta' => $meta]);

        return $voucher;
    }

    public function has(string $voucher): bool
    {
        return $this->filesystem->has($voucher);
    }

    public function retrieve(string $voucher): BulkyItemInterface|null
    {
        $file = $this->filesystem->get($voucher);

        if (null === $file) {
            return null;
        }

        $meta = $file->getExtraMetadata()['storage_meta'] ?? [];
        $class = $meta['class'] ?? null;

        if (null === $class || !class_exists($class) || !is_a($class, BulkyItemInterface::class, true)) {
            return null;
        }

        /** @var BulkyItemInterface $class */
        return $class::restore(
            $this->filesystem->readStream($voucher),
            $meta['item'] ?? []
        );
    }

    public function prune(): void
    {
        $oldestToKeep = (int) (new \DateTimeImmutable())
            ->sub(new \DateInterval('P'.$this->retentionPeriodInDays.'D'))
            ->format('Ymd')
        ;

        foreach ($this->filesystem->listContents('')->directories() as $directory) {
            $date = (int) $directory->getName();

            if ($date < $oldestToKeep) {
                $this->filesystem->deleteDirectory($directory->getPath());
            }
        }
    }

    public static function validateVoucherFormat(string $voucher): bool
    {
        if (!preg_match('@^\d{8}/@', $voucher)) {
            return false;
        }

        return Uuid::isValid(substr($voucher, 9));
    }
}
