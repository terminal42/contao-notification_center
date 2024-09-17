<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FileItemFactory
{
    public function __construct(private readonly MimeTypeGuesserInterface $mimeTypeGuesser)
    {
    }

    public function createFromLocalPath(string $path): FileItem
    {
        if (!(new Filesystem())->exists($path)) {
            throw new \InvalidArgumentException(\sprintf('The file "%s" does not exist.', $path));
        }

        $name = basename($path);
        $mimeType = (string) $this->mimeTypeGuesser->guessMimeType($path);
        $size = (int) filesize($path);

        return FileItem::fromPath($path, $name, $mimeType, $size);
    }

    public function createFromVfsFilesystemItem(FilesystemItem $file, VirtualFilesystemInterface $virtualFilesystem): FileItem
    {
        $stream = $virtualFilesystem->readStream($file->getPath());

        return FileItem::fromStream($stream, $file->getName(), $file->getMimeType(), $file->getFileSize());
    }
}
