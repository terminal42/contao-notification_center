<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Controller\FrontendModule;

use Codefog\HasteBundle\Formatter;
use Codefog\HasteBundle\UrlParser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\OptIn\OptInInterface;
use Contao\CoreBundle\OptIn\OptInTokenInterface;
use Contao\FrontendTemplate;
use Contao\MemberModel;
use Contao\ModuleModel;
use Contao\ModuleRegistration;
use Contao\OptInModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsFrontendModule('registrationNotificationCenter', 'user', 'member_default')]
class RegistrationController extends ModuleRegistration
{
    public function __construct(private NotificationCenter $notificationCenter, private RequestStack $requestStack, private OptInInterface $optIn, private Formatter $formatter, private UrlParser $urlParser)
    {
    }

    public function __invoke(ModuleModel $model, string $section): Response
    {
        parent::__construct($model, $section);

        // Force the Core module to send an activation e-mail which will cause sendActivationMail() to be called
        // where we can override the core behaviour and ALWAYS send a notification for a new user, no matter if that
        // checkbox was set or not (it's hidden in the palette for our module)
        $this->reg_activate = true;

        return new Response($this->generate());
    }

    protected function sendActivationMail($arrData): void
    {
        $optInToken = null;

        // If opt-in is enabled, create the opt-in token and thus the ##link## simple token
        if ($this->nc_registration_auto_activate) {
            $optInToken = $this->optIn->create('reg', $arrData['email'], ['tl_member' => [$arrData['id']]]);
        }

        $this->sendNotification((int) $arrData['id'], $optInToken);
    }

    /**
     * Re-send the activation mail.
     */
    protected function resendActivationMail(MemberModel $objMember): void
    {
        if (!$objMember->disable) {
            return;
        }

        $this->strTemplate = 'mod_message';
        $this->Template = new FrontendTemplate($this->strTemplate);

        $optInToken = null;
        $models = OptInModel::findByRelatedTableAndIds('tl_member', [$objMember->id]);

        foreach ($models as $model) {
            // Look for a valid, unconfirmed token
            if (($token = $this->optIn->find($model->token)) && $token->isValid() && !$token->isConfirmed()) {
                $optInToken = $token;
                break;
            }
        }

        if (null === $optInToken) {
            return;
        }

        $this->sendNotification((int) $objMember->id, $optInToken);

        // Confirm activation
        $this->Template->type = 'confirm';
        $this->Template->message = $GLOBALS['TL_LANG']['MSC']['resendActivation'];
    }

    private function sendNotification(int $memberId, OptInTokenInterface $optInToken = null): void
    {
        if (!$this->nc_notification) {
            return;
        }

        // Prepare the simple token data
        $tokens = [];

        if ($optInToken instanceof OptInTokenInterface) {
            $tokens['activation'] = $optInToken->getIdentifier();
            $tokens['token'] = $optInToken->getIdentifier(); // Unifying the names with other notification types
        }

        if (($request = $this->requestStack->getCurrentRequest()) instanceof Request) {
            $tokens['domain'] = $request->getHttpHost();

            if ($optInToken instanceof OptInTokenInterface) {
                $tokens['link'] = $this->urlParser->addQueryString('token='.$optInToken->getIdentifier(), $request->getUri());
            }
        }

        $member = MemberModel::findByPk($memberId);

        if (null !== $member) {
            foreach ($member->row() as $k => $v) {
                $tokens['member_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
                $tokens['member_raw_'.$k] = $v;
            }
        }

        $this->notificationCenter->sendNotification((int) $this->nc_notification, $tokens);
    }
}
