<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Controller\FrontendModule;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\Environment;
use Contao\Idna;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Terminal42\NotificationCenterBundle\Legacy\LostPasswordModule;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

#[AsFrontendModule('lostPasswordNotificationCenter', 'user', 'mod_lostPassword')]
class LostPasswordController extends LostPasswordModule
{
    public function __construct(
        private readonly ContentUrlGenerator $contentUrlGenerator,
        private readonly NotificationCenter $notificationCenter,
        private readonly Formatter $formatter,
    ) {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        return new Response($this->generate());
    }

    protected function sendPasswordLink($objMember): void
    {
        $optIn = System::getContainer()->get('contao.opt_in');
        $optInToken = $optIn->create('pw', $objMember->email, ['tl_member' => [$objMember->id]]);

        // Prepare the simple tokens
        $tokens = [];
        $tokens['domain'] = Idna::decode(Environment::get('host'));
        $tokens['token'] = $optInToken->getIdentifier();
        $tokens['recipient_email'] = $objMember->email;

        // Generate a custom target link URL, if any
        if ($this->nc_lost_password_jumpTo && null !== ($targetPage = PageModel::findPublishedById($this->nc_lost_password_jumpTo))) {
            $tokens['link'] = $this->contentUrlGenerator->generate($targetPage, ['token' => $optInToken->getIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $tokens['link'] = Idna::decode(Environment::get('url')).Environment::get('requestUri').(str_contains((string) Environment::get('requestUri'), '?') ? '&' : '?').'token='.$optInToken->getIdentifier();
        }

        // Add member tokens
        foreach ($objMember->row() as $k => $v) {
            $tokens['member_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
            $tokens['member_raw_'.$k] = $v;
        }

        $receipts = $this->notificationCenter->sendNotification((int) $this->nc_notification, $tokens);

        /** @var Receipt $receipt */
        foreach ($receipts as $receipt) {
            if (!$receipt->wasDelivered()) {
                System::getContainer()->get('monolog.logger.contao.error')->error("Could not send notification in lost password module ID $this->id. Error: {$receipt->getException()->getMessage()}");
            }
        }

        if ($receipts->wereAllDelivered()) {
            System::getContainer()->get('monolog.logger.contao.access')->info('A new password has been requested for user ID '.$objMember->id.' ('.Idna::decodeEmail($objMember->email).')');
        }

        // Check whether there is a jumpTo page
        if (($objJumpTo = $this->objModel->getRelated('jumpTo')) instanceof PageModel) {
            $this->jumpToOrReload($objJumpTo->row());
        }

        $this->reload();
    }
}
