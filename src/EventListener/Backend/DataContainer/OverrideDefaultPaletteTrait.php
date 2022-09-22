<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend\DataContainer;

use Contao\DataContainer;

trait OverrideDefaultPaletteTrait
{
    private function overrideDefaultPaletteForGateway(int $gatewayId, string $table): void
    {
        if (0 === $gatewayId) {
            return;
        }

        $gatewayType = $this->connection->createQueryBuilder()
            ->select('type')
            ->from('tl_nc_gateway')
            ->where('id = :id')
            ->setParameter('id', $gatewayId)
            ->executeQuery()
            ->fetchOne()
        ;

        if (false === $gatewayType) {
            return;
        }

        $GLOBALS['TL_DCA'][$table]['palettes']['default'] = $GLOBALS['TL_DCA'][$table]['palettes'][$gatewayType];
    }

    private function getCurrentRecord(DataContainer $dataContainer): array
    {
        // Contao 4.13 compat
        if (!method_exists($dataContainer, 'getCurrentRecord')) {
            return $this->queryRecord($dataContainer->table, (int) $dataContainer->id);
        }

        return $dataContainer->getCurrentRecord($dataContainer->id);
    }

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
