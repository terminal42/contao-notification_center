<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Terminal42\NotificationCenterBundle\Exception\BulkyItem\InvalidFileItemException;

class FileItemFactory
{
    public function __construct(private readonly MimeTypeGuesserInterface|null $mimeTypeGuesser = null)
    {
    }

    /**
     * @throws InvalidFileItemException if the information cannot be fetched automatically (e.g. missing mime type guesser service)
     */
    public function createFromLocalPath(string $path): FileItem
    {
        if (!(new Filesystem())->exists($path)) {
            throw new InvalidFileItemException(\sprintf('The file "%s" does not exist.', $path));
        }

        $name = basename($path);
        $mimeType = (string) $this->mimeTypeGuesser?->guessMimeType($path);
        $size = (int) filesize($path);

        return FileItem::fromPath($path, $name, $mimeType, $size);
    }

    /**
     * @throws InvalidFileItemException
     */
    public function createFromVfsFilesystemItem(FilesystemItem $file, VirtualFilesystemInterface $virtualFilesystem): FileItem
    {
        $stream = $virtualFilesystem->readStream($file->getPath());

        return FileItem::fromStream($stream, $file->getName(), $file->getMimeType(), $file->getFileSize());
    }
}
