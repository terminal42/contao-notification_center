<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Terminal42\NotificationCenterBundle\Event\GetMessageTypeForModuleConfigEvent;
use Terminal42\NotificationCenterBundle\MessageType\LostPasswordMessageType;
use Terminal42\NotificationCenterBundle\MessageType\MemberActivationMessageType;
use Terminal42\NotificationCenterBundle\MessageType\MemberRegistrationMessageType;

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
        if ('lostPasswordNotificationCenter' === $event->getModuleConfig()->getType() && 'nc_notification' === $event->getField()) {
            $event->setMessageType(LostPasswordMessageType::NAME);

            return;
        }

        if ('registrationNotificationCenter' === $event->getModuleConfig()->getType()) {
            if ('nc_notification' === $event->getField()) {
                $event->setMessageType(MemberRegistrationMessageType::NAME);

                return;
            }

            if ('nc_activation_notification' === $event->getField()) {
                $event->setMessageType(MemberActivationMessageType::NAME);

                return;
            }
        }
    }
}
