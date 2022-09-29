<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terminal42\NotificationCenterBundle\Event\GetMessageTypeForModuleConfigEvent;
use Terminal42\NotificationCenterBundle\MessageType\LostPasswordMessageType;

class MessageTypeForModuleConfigSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GetMessageTypeForModuleConfigEvent::class => '__invoke',
        ];
    }

    public function __invoke(GetMessageTypeForModuleConfigEvent $event): void
    {
        if ('lostPassword' === $event->getModuleConfig()->getType()) {
            $event->setMessageType(LostPasswordMessageType::NAME);
        }
    }
}
