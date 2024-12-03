<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class MailerTransportMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_nc_gateway'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_nc_gateway');

        if (!isset($columns['mailertransport'])) {
            return false;
        }

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_nc_gateway WHERE mailerTransport=''") > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("ALTER TABLE tl_nc_gateway CHANGE mailerTransport mailerTransport varchar(64) DEFAULT NULL");
        $this->connection->executeStatement("UPDATE tl_nc_gateway SET mailerTransport = NULL WHERE mailerTransport = ''");

        return $this->createResult(true);
    }
}
