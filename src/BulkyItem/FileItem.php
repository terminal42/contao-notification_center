<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

use Symfony\Component\Filesystem\Filesystem;
use Terminal42\NotificationCenterBundle\Exception\BulkyItem\InvalidFileItemException;

class FileItem implements BulkyItemInterface
{
    /**
     * @param resource $contents
     *
     * @throws InvalidFileItemException
     */
    private function __construct(
        private $contents,
        private readonly string $name,
        private readonly string $mimeType,
        private readonly int $size,
    ) {
        $this->assert('' !== $this->name, 'Name must not be empty');
        $this->assert('' !== $this->mimeType, 'Mime type must not be empty');
        $this->assert($this->size >= 0, 'File size must not be smaller than 0');
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

    /**
     * @throws InvalidFileItemException
     */
    public static function fromPath(string $path, string $name, string $mimeType, int $size): self
    {
        if (!(new Filesystem())->exists($path)) {
            throw new InvalidFileItemException(\sprintf('The file "%s" does not exist.', $path));
        }

        return new self(fopen($path, 'r'), $name, $mimeType, $size);
    }

    /**
     * @param resource $resource
     *
     * @throws InvalidFileItemException
     */
    public static function fromStream($resource, string $name, string $mimeType, int $size): self
    {
        if (!\is_resource($resource)) {
            throw new InvalidFileItemException('$contents must be a resource.');
        }

        return new self($resource, $name, $mimeType, $size);
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new InvalidFileItemException($message);
        }
    }
}
