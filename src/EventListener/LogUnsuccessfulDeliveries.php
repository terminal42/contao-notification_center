<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\ReceiptEvent;

#[AsEventListener]
class LogUnsuccessfulDeliveries
{
    public function __construct(private readonly LoggerInterface|null $contaoErrorLogger)
    {
    }

    public function __invoke(ReceiptEvent $event): void
    {
        if (null === $this->contaoErrorLogger) {
            return;
        }

        $receipt = $event->receipt;

        if ($receipt->wasDelivered()) {
            return;
        }

        $exception = $receipt->getException();

        $this->contaoErrorLogger->error($exception->getMessage(), ['exception' => $exception]);
    }
}
