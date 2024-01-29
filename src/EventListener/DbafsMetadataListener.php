<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\CoreBundle\Filesystem\Dbafs\RetrieveDbafsMetadataEvent;
use Contao\CoreBundle\Filesystem\Dbafs\StoreDbafsMetadataEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;

class DbafsMetadataListener
{
    #[AsEventListener]
    public function enhanceMetadata(RetrieveDbafsMetadataEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        $row = $event->getRow();

        $meta = json_decode($row['storage_meta'] ?? '{}', true);

        if (!\is_array($meta)) {
            return;
        }

        $event->set('storage_meta', $meta);
    }

    #[AsEventListener]
    public function normalizeMetadata(StoreDbafsMetadataEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }

        $meta = json_encode($event->getExtraMetadata()['storage_meta'] ?? []);

        if (false === $meta) {
            $meta = '{}';
        }

        $event->set('storage_meta', $meta);
    }

    private function supports(RetrieveDbafsMetadataEvent|StoreDbafsMetadataEvent $event): bool
    {
        return Terminal42NotificationCenterExtension::BULKY_ITEMS_VFS_TABLE_NAME === $event->getTable();
    }
}
