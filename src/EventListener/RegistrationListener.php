<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\MemberModel;
use Contao\Module;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\NotificationCenter;

class RegistrationListener
{
    public function __construct(private NotificationCenter $notificationCenter, private RequestStack $requestStack, private Formatter $formatter)
    {
    }

    #[AsHook('activateAccount')]
    public function sendActivationEmail(MemberModel $member, Module $module): void
    {
        if (!$module->nc_activation_notification) {
            return;
        }

        $tokens = [];

        if (($request = $this->requestStack->getCurrentRequest()) instanceof Request) {
            $tokens['domain'] = $request->getHttpHost();
        }

        foreach ($member->row() as $k => $v) {
            $tokens['member_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
        }

        $this->notificationCenter->sendNotification((int) $module->nc_activation_notification, $tokens);
    }
}
