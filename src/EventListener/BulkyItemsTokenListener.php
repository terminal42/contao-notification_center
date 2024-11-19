<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\EventListener;

use Contao\StringUtil;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemInterface;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Token;
use Twig\Environment;

class BulkyItemsTokenListener
{
    public function __construct(
        private readonly BulkyItemStorage $bulkyItemStorage,
        private readonly TokenDefinitionFactoryInterface $tokenDefinitionFactory,
        private readonly Environment $twig,
    ) {
    }

    #[AsEventListener]
    public function onGetTokenDefinitions(GetTokenDefinitionsForNotificationTypeEvent $event): void
    {
        $event
            ->addTokenDefinition($this->tokenDefinitionFactory->create(AnythingTokenDefinition::class, 'file_item_html_*', 'file_item_html_*'))
            ->addTokenDefinition($this->tokenDefinitionFactory->create(AnythingTokenDefinition::class, 'file_item_text_*', 'file_item_text_*'))
        ;
    }

    #[AsEventListener]
    public function onCreateParcel(CreateParcelEvent $event): void
    {
        if (!$event->getParcel()->hasStamp(TokenCollectionStamp::class) || !$event->getParcel()->getStamp(BulkyItemsStamp::class)) {
            return;
        }

        $tokenCollection = $event->getParcel()->getStamp(TokenCollectionStamp::class)->tokenCollection;

        foreach ($tokenCollection as $token) {
            $items = $this->extractFileItems($token, $event->getParcel()->getStamp(BulkyItemsStamp::class));

            if ([] === $items) {
                continue;
            }

            $tokenCollection->addToken($this->createFileToken($event->getParcel(), $token, $items, 'html', HtmlTokenDefinition::class));
            $tokenCollection->addToken($this->createFileToken($event->getParcel(), $token, $items, 'text', TextTokenDefinition::class));
        }
    }

    /**
     * @param array<string, BulkyItemInterface> $items
     */
    private function createFileToken(Parcel $parcel, Token $token, array $items, string $format, string $tokenDefinitionClass): Token
    {
        $content = $this->twig->render('@Contao/notification_center/file_token.html.twig', [
            'files' => $items,
            'parcel' => $parcel,
            'format' => $format,
        ]);

        $tokenName = 'file_item_'.$format.'_'.$token->getName();

        return $this->tokenDefinitionFactory->create($tokenDefinitionClass, $tokenName, $tokenName)
            ->createToken($tokenName, $content)
        ;
    }

    /**
     * @return array<string, BulkyItemInterface>
     */
    private function extractFileItems(Token $token, BulkyItemsStamp $bulkyItemsStamp): array
    {
        $possibleVouchers = StringUtil::trimsplit(',', $token->getParserValue());
        $items = [];

        foreach ($possibleVouchers as $possibleVoucher) {
            // Shortcut: Not a possibly bulky item voucher anyway - continue
            if (!BulkyItemStorage::validateVoucherFormat($possibleVoucher)) {
                continue;
            }

            if (!$bulkyItemsStamp->has($possibleVoucher)) {
                continue;
            }

            if ($item = $this->bulkyItemStorage->retrieve($possibleVoucher)) {
                $items[$possibleVoucher] = $item;
            }
        }

        return $items;
    }
}
