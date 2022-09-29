<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

class ReceiptEvent extends Event
{
    public function __construct(public readonly Receipt $receipt)
    {
    }
}
