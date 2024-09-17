<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Codefog\HasteBundle\Formatter;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\Module;
use Soundasleep\Html2Text;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Twig\Environment;

class UpdatePersonalDataListener
{
    private const OLD_SESSION_DATA_KEY = 'notification_center_old_data';

    public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly RequestStack $requestStack,
        private readonly Formatter $formatter,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Environment $twig,
    ) {
    }

    #[AsCallback('tl_member', 'config.onload')]
    public function storePersonalData(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$request instanceof Request || !$this->scopeMatcher->isFrontendRequest($request) || !$user instanceof FrontendUser) {
            return;
        }

        $request->getSession()->set(self::OLD_SESSION_DATA_KEY, $user->getData());
    }

    /**
     * @param array<int|string> $data
     */
    #[AsHook('updatePersonalData')]
    public function updatePersonalData(FrontendUser $member, array $data, Module $module): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $oldData = $request->getSession()->get(self::OLD_SESSION_DATA_KEY, []);
        $request->getSession()->remove(self::OLD_SESSION_DATA_KEY);

        if (!$module->nc_notification) {
            return;
        }

        $tokens = [];
        $changes = [];

        foreach ($member->getData() as $k => $v) {
            $tokens['member_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
            $tokens['member_raw_'.$k] = $v;
        }

        foreach ($oldData as $k => $v) {
            $tokens['member_old_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
            $tokens['member_old_raw_'.$k] = $v;

            // Do not generate any changed_* tokens or comparisons for fields that were
            // not submitted.
            if (!isset($data[$k])) {
                continue;
            }

            if ($tokens['member_'.$k] !== $tokens['member_old_'.$k]) {
                $tokens['changed_'.$k] = true;
                $changes[$k] = [
                    'before' => $tokens['member_old_'.$k],
                    'after' => $tokens['member_'.$k],
                ];
            } else {
                $tokens['changed_'.$k] = false;
            }
        }

        $tokens['comparison_text'] = $this->renderChanges($changes, 'text');
        $tokens['comparison_html'] = $this->renderChanges($changes, 'html');

        $this->notificationCenter->sendNotification((int) $module->nc_notification, $tokens);
    }

    /**
     * @param array<int|string, array{before: mixed, after: mixed}> $changes
     */
    private function renderChanges(array $changes, string $format): string
    {
        if (0 === \count($changes)) {
            return '';
        }

        $html = $this->twig->render('@Terminal42NotificationCenter/table.html.twig', ['changes' => $changes]);

        return match ($format) {
            'text' => Html2Text::convert($html),
            default => $html,
        };
    }
}
