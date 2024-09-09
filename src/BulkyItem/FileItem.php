<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Symfony\Component\Filesystem\Filesystem;

class FileItem implements BulkyItemInterface
{
    /**
     * @param resource $contents
     */
    private function __construct(
        private $contents,
        private readonly string $name,
        private readonly string $mimeType,
        private readonly int $size,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function getMeta(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->mimeType,
            'size' => $this->size,
        ];
    }

    public static function restore($contents, array $meta): BulkyItemInterface
    {
        return new self($contents, $meta['name'], $meta['type'], $meta['size']);
    }

    public static function fromPath(string $path, string|null $name = null, string|null $mimeType = null, int|null $size = null): self
    {
        if (!(new Filesystem())->exists($path)) {
            throw new \InvalidArgumentException(\sprintf('The file "%s" does not exist.', $path));
        }

        if (null === $name) {
            $name = basename($path);
        }

        if (null === $mimeType) {
            $mimeType = mime_content_type($path);
        }

        if (null === $size) {
            $size = (int) filesize($path);
        }

        return new self(fopen($path, 'r'), $name, $mimeType, $size);
    }

    /**
     * @param resource $resource
     */
    public static function fromStream($resource, string $name, string $mimeType, int $size): self
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('$contents must be a resource.');
        }

        return new self($resource, $name, $mimeType, $size);
    }
}
