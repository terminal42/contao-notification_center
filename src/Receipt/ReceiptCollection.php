<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Receipt;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<Receipt>
 */
class ReceiptCollection extends AbstractCollection
{
    public function wereAllDelivered(): bool
    {
        foreach ($this as $receipt) {
            if (!$receipt->wasDelivered()) {
                return false;
            }
        }

        return true;
    }

    public function getType(): string
    {
        return Receipt::class;
    }
}
