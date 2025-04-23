<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Receipt\AsynchronousReceipt;

class AsynchronousReceiptEvent extends Event
{
    public function __construct(public readonly AsynchronousReceipt $receipt)
    {
    }
}
