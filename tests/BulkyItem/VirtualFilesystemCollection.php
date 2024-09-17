<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\BulkyItem;

use Contao\CoreBundle\Filesystem\VirtualFilesystem;

class VirtualFilesystemCollection
{
    /**
     * @param array<string, VirtualFilesystem> $vfs
     */
    public function __construct(private array $vfs = [])
    {
    }

    public function get(string $name): VirtualFilesystem
    {
        return $this->vfs[$name];
    }

    public function add(VirtualFilesystem $vfs): self
    {
        $this->vfs[$vfs->getPrefix()] = $vfs;

        return $this;
    }
}
