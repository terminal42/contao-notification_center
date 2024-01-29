<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;

#[AsEventListener]
class DisableDeliveryListener
{
    public function __invoke(CreateParcelEvent $event): void
    {
        $messageConfig = $event->getParcel()->getMessageConfig();

        if (!$messageConfig->isPublished()) {
            $event->disableDelivery();

            return;
        }

        $now = new \DateTimeImmutable();

        if (null !== ($start = $messageConfig->getStart()) && $now < $start) {
            $event->disableDelivery();

            return;
        }

        if (null !== ($stop = $messageConfig->getStop()) && $now >= $stop) {
            $event->disableDelivery();
        }
    }
}
