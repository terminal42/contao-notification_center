<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class EmailGatewayMigration extends AbstractMigration
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

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_nc_gateway WHERE type='email'") > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_nc_gateway SET type='mailer' WHERE type='email'");

        return $this->createResult(true);
    }
}
