<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\PageModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsEvent;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailToken;
use Terminal42\NotificationCenterBundle\Token\StringToken;

class AdminEmailTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GetTokenDefinitionsEvent::class => 'onGetTokenDefinitions',
            CreateParcelEvent::class => 'onCreateParcel',
        ];
    }

    public function onGetTokenDefinitions(GetTokenDefinitionsEvent $event): void
    {
        $event->addTokenDefinition($this->getTokenDefinition());
    }

    public function onCreateParcel(CreateParcelEvent $event): void
    {
        if (!$event->getParcel()->hasStamp(TokenCollectionStamp::class)) {
            return;
        }

        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        $pageModel = $request->attributes->get('pageModel');

        if (!$pageModel instanceof PageModel) {
            return;
        }

        $pageModel->loadDetails();

        $event->getParcel()->getStamp(TokenCollectionStamp::class)->tokenCollection->add(
            new StringToken($pageModel->adminEmail, 'admin_email', $this->getTokenDefinition()->getDefinitionName())
        );
    }

    private function getTokenDefinition(): EmailToken
    {
        return new EmailToken('admin_email', 'admin_email');
    }
}
