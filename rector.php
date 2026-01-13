<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    // Request stack constructor argument is not available in Contao 4.13
    $rectorConfig->skip([PushRequestToRequestStackConstructorRector::class]);
};
