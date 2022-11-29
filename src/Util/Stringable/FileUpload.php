<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util\Stringable;

class FileUpload implements \Stringable
{
    private function __construct(private string $name, private string $tmpName, private string $type, private int $size)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public static function fromString(string $data): self
    {
        $file = json_decode($data, true);

        if (!\is_array($file)) {
            throw new \InvalidArgumentException('Invalid file upload data.');
        }

        return self::fromSuperGlobal($file);
    }

    public static function fromSuperGlobal(array $file): self
    {
        if (!isset($file['name']) || !isset($file['tmp_name']) || !isset($file['type']) || !isset($file['size'])) {
            throw new \InvalidArgumentException('Invalid file upload data.');
        }

        return new self((string) $file['name'], (string) $file['tmp_name'], (string) $file['type'], (int) $file['size']);
    }

    public function __toString()
    {
        return json_encode([
            'name' => $this->name,
            'tmp_name' => $this->tmpName,
            'type' => $this->type,
            'size' => $this->size,
        ]);
    }
}
