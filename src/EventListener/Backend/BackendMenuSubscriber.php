<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener\Backend;

use Contao\CoreBundle\Event\MenuEvent;
use Contao\System;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class BackendMenuSubscriber implements EventSubscriberInterface
{
    public function __construct(private Packages $packages)
    {
    }

    public function __invoke(MenuEvent $event): void
    {
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $GLOBALS['TL_CSS'][] = trim($this->packages->getUrl(
            'backend.css',
            'terminal42_notification_center'
        ), '/');

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
