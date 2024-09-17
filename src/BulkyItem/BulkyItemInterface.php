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
     * Return a name for the item.
     */
    public function getName(): string;

    /**
     * Optional meta data to an item. Must be serializable.
     *
     * @return array<mixed>
     */
    public function getMeta(): array;

    /**
     * Restores the item from the storage.
     *
     * @param resource     $contents
     * @param array<mixed> $meta
     */
    public static function restore($contents, array $meta): self;
}
