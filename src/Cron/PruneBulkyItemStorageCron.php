<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;

#[AsCronJob('daily')]
class PruneBulkyItemStorageCron
{
    public function __construct(private readonly BulkyItemStorage $storage)
    {
    }

    public function __invoke(): void
    {
        $this->storage->prune();
    }
}
