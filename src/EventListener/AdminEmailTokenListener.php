<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

class AdminEmailTokenListener
{
    public function __construct(private RequestStack $requestStack, private TokenDefinitionFactoryInterface $tokenDefinitionFactory, private ContaoFramework $contaoFramework)
    {
    }

    #[AsEventListener]
    public function onGetTokenDefinitions(GetTokenDefinitionsForNotificationTypeEvent $event): void
    {
        $event->addTokenDefinition($this->getTokenDefinition());
    }

    #[AsEventListener]
    public function onCreateParcel(CreateParcelEvent $event): void
    {
        if (!$event->getParcel()->hasStamp(TokenCollectionStamp::class)) {
            return;
        }

        $email = $this->getEmailFromPage();

        if (null === $email) {
            $email = $this->getEmailFromConfig();
        }

        if (null === $email) {
            return;
        }

        $event->getParcel()->getStamp(TokenCollectionStamp::class)->tokenCollection->add(
            $this->getTokenDefinition()->createToken('admin_email', $email)
        );
    }

    private function getEmailFromPage(): string|null
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return null;
        }

        $pageModel = $request->attributes->get('pageModel');

        if (!$pageModel instanceof PageModel) {
            return null;
        }

        $pageModel->loadDetails();

        return $pageModel->adminEmail ?: null;
    }

    private function getEmailFromConfig(): string|null
    {
        $email = $this->contaoFramework->getAdapter(Config::class)->get('adminEmail');

        return !\is_string($email) ? null : $email;
    }

    private function getTokenDefinition(): TokenDefinitionInterface
    {
        return $this->tokenDefinitionFactory->create(EmailTokenDefinition::class, 'admin_email', 'admin_email');
    }
}
