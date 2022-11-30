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
use Symfony\Component\Security\Core\Security;
use Terminal42\NotificationCenterBundle\MessageType\MemberPersonalDataMessageType;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Twig\Environment;

class UpdatePersonalDataListener
{
    private const OLD_SESSION_DATA_KEY = 'notification_center_old_data';

    public function __construct(private NotificationCenter $notificationCenter, private RequestStack $requestStack, private Formatter $formatter, private ScopeMatcher $scopeMatcher, private Security $security, private Environment $twig)
    {
    }

    #[AsCallback('tl_member', 'config.onload')]
    public function storePersonalData(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();

        if (!$request instanceof Request || !$this->scopeMatcher->isFrontendRequest($request) || !$user instanceof FrontendUser) {
            return;
        }

        $request->getSession()->set(self::OLD_SESSION_DATA_KEY, $user->getData());
    }

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

        $rawTokens = [];
        $changes = [];

        foreach ($member->getData() as $k => $v) {
            $rawTokens['member_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);
        }

        foreach ($oldData as $k => $v) {
            $rawTokens['member_old_'.$k] = $this->formatter->dcaValue('tl_member', $k, $v);

            // Do not generate any changed_* tokens or comparisons for fields that were not submitted.
            if (!isset($data[$k])) {
                continue;
            }

            if ($rawTokens['member_'.$k] !== $rawTokens['member_old_'.$k]) {
                $rawTokens['changed_'.$k] = true;
                $changes[$k] = [
                    'before' => $rawTokens['member_old_'.$k],
                    'after' => $rawTokens['member_'.$k],
                ];
            } else {
                $rawTokens['changed_'.$k] = false;
            }
        }

        $rawTokens['comparison_text'] = $this->renderChanges($changes, 'text');
        $rawTokens['comparison_html'] = $this->renderChanges($changes, 'html');

        $tokens = $this->notificationCenter->createTokenCollectionFromArray($rawTokens, MemberPersonalDataMessageType::NAME);
        $this->notificationCenter->sendNotification((int) $module->nc_notification, $tokens);
    }

    private function renderChanges(array $changes, string $format): string
    {
        if (0 === \count($changes)) {
            return '';
        }

        $html = $this->twig->render('@Terminal42NotificationCenter/table.html.twig', ['changes' => $changes]);

        return match ($format) {
            'text' => Html2Text::convert($html),
            default => $html
        };
    }
}
