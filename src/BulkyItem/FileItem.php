<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

class FileItem implements BulkyItemInterface
{
    /**
     * @param resource $contents
     */
    private function __construct(private $contents, private string $name, private string $mimeType, private int $size)
    {
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

    public static function fromPath(string $path, string $name, string $mimeType, int $size): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $path));
        }

        return new self(fopen($path, 'r'), $name, $mimeType, $size);
    }

    public static function fromStream($resouce, string $name, string $mimeType, int $size): self
    {
        if (!\is_resource($resouce)) {
            throw new \InvalidArgumentException('$contents must be a resource.');
        }

        return new self($resouce, $name, $mimeType, $size);
    }
}
