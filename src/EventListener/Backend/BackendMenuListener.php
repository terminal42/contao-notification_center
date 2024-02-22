<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class BackendMenuListener
{
    public function __construct(private readonly Packages $packages)
    {
    }

    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $notificationCenter = $tree->getChild('notification_center');

        // No permissions for the NC
        if (null === $notificationCenter) {
            return;
        }

        $GLOBALS['TL_CSS'][] = trim($this->packages->getUrl(
            'backend.css',
            'terminal42_notification_center',
        ), '/');

        // Moves the NC after "content" (or 1 if that does not exist)
        $targetPosition = array_search('content', array_keys($tree->getChildren()), true);
        $targetPosition = false === $targetPosition ? 1 : $targetPosition + 1;
        $manipulator = new MenuManipulator();
        $manipulator->moveToPosition($notificationCenter, $targetPosition);
    }
}
