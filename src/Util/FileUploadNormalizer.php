<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Util;

use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\StringUtil;
use Contao\Validator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\Uid\Uuid;

class FileUploadNormalizer
{
    private const REQUIRED_KEYS = ['name', 'type', 'tmp_name', 'error', 'size', 'uploaded', 'uuid'];

    public function __construct(
        private readonly string $projectDir,
        private readonly MimeTypeGuesserInterface $mimeTypeGuesser,
        private readonly VirtualFilesystemInterface $filesStorage,
    ) {
    }

    /**
     * This service helps to normalize file upload widget values. Some return an array, others just uuids, some only file
     * paths. This method is designed to bring them all to the Contao FormUpload value style.
     *
     * @return array<string, array<array{name: string, type: string, tmp_name: string, error: int, size: int, uploaded: bool, uuid: ?string, stream: ?resource}>>
     */
    public function normalize(array $files): array
    {
        $standardizedPerKey = [];

        foreach ($files as $k => $file) {
            switch (true) {
                case $this->hasRequiredKeys($file):
                    $file['stream'] = $this->fopen($file['tmp_name']);
                    $standardizedPerKey[$k][] = $file;
                    break;
                case $this->isPhpUpload($file):
                    $standardizedPerKey[$k][] = $this->fromPhpUpload($file);
                    break;
                case \is_array($file):
                    foreach ($this->normalize($file) as $nestedFiles) {
                        $standardizedPerKey[$k] = array_merge($standardizedPerKey[$k] ?? [], $nestedFiles);
                    }
                    break;
                case null !== ($uuid = $this->extractUuid($file)):
                    $standardizedPerKey[$k][] = $this->fromUuid($uuid);
                    break;
                case null !== ($filePath = $this->extractFilePath($file)):
                    $standardizedPerKey[$k][] = $this->fromFile($filePath);
                    break;
            }
        }

        return $standardizedPerKey;
    }

    private function fromFile(string $file): array
    {
        return [
            'name' => basename($file),
            'type' => $this->mimeTypeGuesser->guessMimeType($file),
            'tmp_name' => $file,
            'error' => 0,
            'size' => false === ($size = filesize($file)) ? 0 : $size,
            'uploaded' => true,
            'uuid' => null,
            'stream' => $this->fopen($file),
        ];
    }

    private function fromUuid(Uuid $uuid): array
    {
        $item = $this->filesStorage->get($uuid);

        if (null === $item) {
            return [];
        }

        return [
            'name' => $item->getName(),
            'type' => $item->getMimeType(),
            'tmp_name' => $item->getPath(),
            'error' => 0,
            'size' => $item->getFileSize(),
            'uploaded' => true,
            'uuid' => $uuid->toRfc4122(),
            'stream' => $this->filesStorage->readStream($uuid),
        ];
    }

    private function hasRequiredKeys(mixed $file): bool
    {
        if (!\is_array($file)) {
            return false;
        }

        return self::REQUIRED_KEYS === array_keys($file);
    }

    private function extractUuid(mixed $candidate): Uuid|null
    {
        if (!Validator::isUuid($candidate)) {
            return null;
        }

        if (Validator::isBinaryUuid($candidate)) {
            $candidate = StringUtil::binToUuid($candidate);
        }

        try {
            return Uuid::isValid($candidate) ? Uuid::fromString($candidate) : Uuid::fromBinary($candidate);
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractFilePath(mixed $file): string|null
    {
        if (!\is_string($file)) {
            return null;
        }

        $file = Path::makeAbsolute($file, $this->projectDir);

        if (!(new Filesystem())->exists($file)) {
            return null;
        }

        return $file;
    }

    /**
     * @return resource|null
     */
    private function fopen(string $file)
    {
        try {
            $handle = @fopen($file, 'r');
        } catch (\Throwable) {
            return null;
        }

        if (false === $handle) {
            return null;
        }

        return $handle;
    }

    private function isPhpUpload(mixed $file): bool
    {
        if (!\is_array($file) || !isset($file['tmp_name'])) {
            return false;
        }

        return is_uploaded_file($file['tmp_name']);
    }

    private function fromPhpUpload(array $file): array
    {
        return [
            'name' => $file['name'],
            'type' => $file['type'],
            'tmp_name' => $file['tmp_name'],
            'error' => 0,
            'size' => $file['size'],
            'uploaded' => true,
            'uuid' => null,
            'stream' => $this->fopen($file['tmp_name']),
        ];
    }
}
