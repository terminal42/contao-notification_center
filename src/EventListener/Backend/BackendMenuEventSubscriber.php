<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BackendMenuEventSubscriber implements EventSubscriberInterface
{
    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        // Moves the NC to second position
        $manipulator = new MenuManipulator();
        $manipulator->moveToPosition($tree->getChild('notification_center'), 1);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MenuEvent::class => '__invoke',
        ];
    }
}
