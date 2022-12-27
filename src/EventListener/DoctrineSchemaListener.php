<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\DependencyInjection\Terminal42NotificationCenterExtension;

#[AsEventListener]
class DoctrineSchemaListener
{
    public function __invoke(GenerateSchemaEventArgs $event): void
    {
        $table = $event->getSchema()->createTable(Terminal42NotificationCenterExtension::BULKY_ITEMS_VFS_TABLE_NAME);

        // Defaults needed for DBAFS
        $table->addColumn('id', Types::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('uuid', Types::BINARY, ['fixed' => true, 'length' => 16, 'notnull' => false]);
        $table->addColumn('pid', Types::BINARY, ['fixed' => true, 'length' => 16, 'notnull' => false]);
        $table->addColumn('tstamp', Types::INTEGER, ['unsigned' => true, 'default' => 0]);
        $table->addColumn('path', Types::STRING, ['fixed' => true, 'length' => 255, 'default' => '']);
        $table->addColumn('type', Types::STRING, ['length' => 16]);
        $table->addColumn('hash', Types::STRING, ['length' => 32, 'default' => '']);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['pid']);
        $table->addUniqueIndex(['uuid']);
        $table->addIndex(['path']);

        // Our custom column for our own meta data
        $table->addColumn('storage_meta', Types::TEXT, ['notnull' => false]);
    }
}
