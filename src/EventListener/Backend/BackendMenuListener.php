<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\Util\MenuManipulator;

class BackendMenuListener
{
    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        // Moves the NC to seecond position
        $manipulator = new MenuManipulator();
        $manipulator->moveToPosition($tree->getChild('notification_center'), 1);
    }
}
