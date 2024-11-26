<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Contao\CoreBundle\Filesystem\ExtraMetadata;
use Contao\CoreBundle\Filesystem\VirtualFilesystemException;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Symfony\Component\HttpFoundation\UriSigner as HttpFoundationUriSigner;
use Symfony\Component\HttpKernel\UriSigner as HttpKernelUriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;

class BulkyItemStorage
{
    public const VOUCHER_REGEX = '^\d{8}/[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$';

    public function __construct(
        private readonly VirtualFilesystemInterface $filesystem,
        private readonly RouterInterface $router,
        private readonly HttpFoundationUriSigner|HttpKernelUriSigner $uriSigner,
        private readonly int $retentionPeriodInDays = 7,
    ) {
    }

    /**
     * Returns the voucher with which you can come back to the storage and get your
     * bulky item back. Would probably also call this a "token" in a real world
     * cloakroom but in order to not mess up the terminology with simple tokens etc.,
     * we're going for voucher here.
     */
    public function store(BulkyItemInterface $item): string
    {
        $voucher = date('Ymd').'/'.Uuid::v4()->toRfc4122();
        $this->filesystem->writeStream($voucher, $item->getContents());

        $meta = [
            'item' => $item->getMeta(),
            'class' => $item::class,
        ];

        $this->filesystem->setExtraMetadata($voucher, new ExtraMetadata(['storage_meta' => $meta]));

        return $voucher;
    }

    public function has(string $voucher): bool
    {
        try {
            return $this->filesystem->has($voucher);
        } catch (VirtualFilesystemException) {
            return false;
        }
    }

    public function retrieve(string $voucher): BulkyItemInterface|null
    {
        try {
            $file = $this->filesystem->get($voucher);
        } catch (VirtualFilesystemException) {
            return null;
        }

        if (null === $file) {
            return null;
        }

        $meta = $file->getExtraMetadata()['storage_meta'] ?? [];
        $class = $meta['class'] ?? null;

        if (null === $class || !class_exists($class) || !is_a($class, BulkyItemInterface::class, true)) {
            return null;
        }

        try {
            $stream = $this->filesystem->readStream($voucher);
        } catch (VirtualFilesystemException) {
            return null;
        }

        /** @var BulkyItemInterface $class */
        return $class::restore(
            $stream,
            $meta['item'] ?? [],
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

    public function generatePublicUri(string $voucher, int|null $ttl = null): string
    {
        return $this->uriSigner->sign(
            $this->router->generate('nc_bulky_item_download', ['voucher' => $voucher], UrlGeneratorInterface::ABSOLUTE_URL),
            time() + $ttl,
        );
    }

    public static function validateVoucherFormat(string $voucher): bool
    {
        return 1 === preg_match('@'.self::VOUCHER_REGEX.'@', $voucher);
    }
}
