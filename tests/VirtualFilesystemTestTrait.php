<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test;

use Contao\CoreBundle\Filesystem\Dbafs\DbafsManager;
use Contao\CoreBundle\Filesystem\MountManager;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\InMemoryDbafs;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\VirtualFilesystemCollection;

trait VirtualFilesystemTestTrait
{
    private function createVfsCollection(): VirtualFilesystemCollection
    {
        $mountManager = (new MountManager())
            ->mount(new InMemoryFilesystemAdapter(), 'files')
            ->mount(new InMemoryFilesystemAdapter(), 'bulky_item')
        ;

        $dbafsManager = new DbafsManager($this->createMock(EventDispatcherInterface::class));
        $dbafsManager->register(new InMemoryDbafs(), 'files');
        $dbafsManager->register(new InMemoryDbafs(), 'bulky_item');

        $vfsCollection = new VirtualFilesystemCollection();
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, 'files'));
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, 'bulky_item'));
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, '')); // Global one

        return $vfsCollection;
    }
}
