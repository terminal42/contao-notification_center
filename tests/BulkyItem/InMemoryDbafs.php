<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\BulkyItem;

use Contao\CoreBundle\Filesystem\Dbafs\ChangeSet\ChangeSet;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsInterface;
use Contao\CoreBundle\Filesystem\FilesystemItem;
use Symfony\Component\Uid\Uuid;

class InMemoryDbafs implements DbafsInterface
{
    /**
     * @var array<string, FilesystemItem>
     */
    private array $records = [];

    /**
     * @var array<string, array<mixed>>
     */
    private array $meta = [];

    public function getPathFromUuid(Uuid $uuid): string|null
    {
        throw new \RuntimeException('Not implemented');
    }

    public function getRecord(string $path): FilesystemItem|null
    {
        if (isset($this->records[$path])) {
            return new FilesystemItem(
                true,
                $path,
                null,
                null,
                null,
                $this->meta[$path] ?? [],
            );
        }

        return null;
    }

    public function getRecords(string $path, bool $deep = false): iterable
    {
        throw new \RuntimeException('Not implemented');
    }

    public function setExtraMetadata(string $path, array $metadata): void
    {
        $this->meta[$path] = $metadata;
    }

    public function sync(string ...$paths): ChangeSet
    {
        foreach ($paths as $path) {
            $this->records[$path] = true;
        }

        return new ChangeSet([], [], []);
    }

    public function getSupportedFeatures(): int
    {
        return DbafsInterface::FEATURES_NONE;
    }
}
