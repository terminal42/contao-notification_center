<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class SimpleTokenMigration extends AbstractMigration
{
    private const REGEX = '/##([a-zA-Z0-9_]+-[a-zA-Z0-9_-]*)##/';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_nc_language'])) {
            return false;
        }

        return [] !== $this->getRowsToUpdate();
    }

    public function run(): MigrationResult
    {
        foreach ($this->getRowsToUpdate() as $rowId => $columns) {
            $rows = $this->connection->fetchAssociative('SELECT '.implode(',', $columns).' FROM tl_nc_language WHERE id=?', [$rowId]);
            $set = [];

            foreach ($rows as $column => $value) {
                $set[$column] = preg_replace_callback(
                    self::REGEX,
                    static fn ($matches) => '##'.str_replace('-', '_', $matches[1]).'##',
                    $value,
                );
            }

            $this->connection->update('tl_nc_language', $set, ['id' => $rowId]);
        }

        return $this->createResult(true);
    }

    private function getRowsToUpdate(): array
    {
        $rowsToUpdate = [];

        foreach ($this->connection->fetchAllAssociative('SELECT * FROM tl_nc_language') as $row) {
            foreach ($row as $column => $value) {
                if (!\is_string($value)) {
                    continue;
                }

                if (preg_match(self::REGEX, $value)) {
                    $rowsToUpdate[$row['id']][] = $this->connection->quoteIdentifier($column);
                }
            }
        }

        return $rowsToUpdate;
    }
}
