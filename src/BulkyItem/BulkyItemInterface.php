<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\BulkyItem;

interface BulkyItemInterface
{
    /**
     * Return a stream to the contents of the item.
     *
     * @return resource
     */
    public function getContents();

    /**
     * Optional meta data to an item. Must be serializable.
     */
    public function getMeta(): array;

    /**
     * Restores the item from the storage.
     *
     * @param resource $contents
     */
    public static function restore($contents, array $meta): self;
}
