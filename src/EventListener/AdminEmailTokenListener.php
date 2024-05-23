<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\StringUtil;
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
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TokenDefinitionFactoryInterface $tokenDefinitionFactory,
        private readonly ContaoFramework $contaoFramework,
    ) {
    }

    #[AsEventListener]
    public function onGetTokenDefinitions(GetTokenDefinitionsForNotificationTypeEvent $event): void
    {
        $event
            ->addTokenDefinition($this->getTokenDefinition('admin_email'))
            ->addTokenDefinition($this->getTokenDefinition('admin_name'))
        ;
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

        $event->getParcel()->getStamp(TokenCollectionStamp::class)->tokenCollection
            ->addToken($this->getTokenDefinition('admin_email')->createToken('admin_email', $email[1]))
            ->addToken($this->getTokenDefinition('admin_name')->createToken('admin_name', $email[0]))
        ;
    }

    private function getEmailFromPage(): array|null
    {
        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return null;
        }

        $pageModel = $request->attributes->get('pageModel');

        if (!$pageModel instanceof PageModel) {
            return null;
        }

        $pageModel->loadDetails();

        return $pageModel->adminEmail ? $this->parseFriendlyEmail($pageModel->adminEmail) : null;
    }

    private function getEmailFromConfig(): array|null
    {
        $email = $this->contaoFramework->getAdapter(Config::class)->get('adminEmail');

        return $email ? $this->parseFriendlyEmail($email) : null;
    }

    private function getTokenDefinition(string $token): TokenDefinitionInterface
    {
        return $this->tokenDefinitionFactory->create(EmailTokenDefinition::class, $token, $token);
    }

    private function parseFriendlyEmail(string $email): array
    {
        return $this->contaoFramework->getAdapter(StringUtil::class)->splitFriendlyEmail($email);
    }
}
