<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\DataContainer;

trait GetCurrentRecordTrait
{
    /**
     * @return array<string, mixed>
     */
    private function getCurrentRecord(DataContainer $dataContainer): array
    {
        // Contao 4.13 compat
        if (!method_exists($dataContainer, 'getCurrentRecord')) {
            return $this->queryRecord($dataContainer->table, (int) $dataContainer->id);
        }

        return $dataContainer->getCurrentRecord($dataContainer->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function queryRecord(string $table, int $id): array
    {
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($table)
            ->where('id = :id')
            ->setParameter('id', $id)
            ->executeQuery()
            ->fetchAssociative()
        ;

        if (false === $result) {
            return [];
        }

        return $result;
    }
}
