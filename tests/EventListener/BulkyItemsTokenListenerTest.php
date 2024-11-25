<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemInterface;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\Event\GetTokenDefinitionsForNotificationTypeEvent;
use Terminal42\NotificationCenterBundle\EventListener\BulkyItemsTokenListener;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\BulkyItemsStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Token;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;
use Twig\Environment;

class BulkyItemsTokenListenerTest extends TestCase
{
    public function testOnGetTokenDefinitions(): void
    {
        $tokenDefinitionFactory = $this->createMock(TokenDefinitionFactoryInterface::class);
        $tokenDefinitionFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(static fn (string $definitionClass, string $tokenName, string $translationKey) => new $definitionClass($tokenName, $translationKey))
        ;

        $event = new GetTokenDefinitionsForNotificationTypeEvent($this->createMock(NotificationTypeInterface::class));
        $listener = new BulkyItemsTokenListener(
            $this->createMock(BulkyItemStorage::class),
            $tokenDefinitionFactory,
            $this->createMock(Environment::class),
        );

        $listener->onGetTokenDefinitions($event);

        $this->assertSame(['file_item_html_*', 'file_item_text_*'], array_keys($event->getTokenDefinitions()));
    }

    public function testOnCreateParcelSkipsIfStampsAreMissing(): void
    {
        $parcel = new Parcel(MessageConfig::fromArray([]));
        $event = new CreateParcelEvent($parcel);

        $listener = new BulkyItemsTokenListener(
            $this->createMock(BulkyItemStorage::class),
            $this->createMock(TokenDefinitionFactoryInterface::class),
            $this->createMock(Environment::class),
        );

        $listener->onCreateParcel($event);

        $this->addToAssertionCount(1); // Ensure no exceptions or errors
    }

    public function testOnCreateParcelProcessesTokens(): void
    {
        $bulkyItemStorage = $this->createMock(BulkyItemStorage::class);
        $bulkyItemStorage
            ->method('retrieve')
            ->willReturn($this->createMock(BulkyItemInterface::class))
        ;

        $tokenDefinitionFactory = $this->createMock(TokenDefinitionFactoryInterface::class);
        $tokenDefinitionFactory
            ->method('create')
            ->willReturnCallback(static fn (string $definitionClass, string $tokenName, string $translationKey) => new $definitionClass($tokenName, $translationKey))
        ;

        $twig = $this->createMock(Environment::class);
        $twig
            ->method('render')
            ->willReturnCallback(
                function (string $template, array $context) {
                    $this->assertSame('@Contao/notification_center/file_token.html.twig', $template);

                    return match ($context['format']) {
                        'html' => 'rendered_html_content',
                        'text' => 'rendered_text_content',
                        default => $this->fail('Invalid format!'),
                    };
                },
            )
        ;

        $tokenCollection = new TokenCollection();
        $tokenCollection->addToken(new Token('form_upload', '20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde', '20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde'));

        $parcel = new Parcel(MessageConfig::fromArray([]));
        $parcel = $parcel->withStamp(new TokenCollectionStamp($tokenCollection));
        $parcel = $parcel->withStamp(new BulkyItemsStamp(['20221228/a10aed4d-abe1-498f-adfc-b2e54fbbcbde']));
        $event = new CreateParcelEvent($parcel);

        $listener = new BulkyItemsTokenListener(
            $bulkyItemStorage,
            $tokenDefinitionFactory,
            $twig,
        );

        $listener->onCreateParcel($event);

        $tokenCollection = $event->getParcel()->getStamp(TokenCollectionStamp::class)?->tokenCollection;
        $this->assertInstanceOf(TokenCollection::class, $tokenCollection);
        $this->assertTrue($tokenCollection->has('file_item_html_form_upload'));
        $this->assertTrue($tokenCollection->has('file_item_text_form_upload'));
        $this->assertSame('rendered_html_content', $tokenCollection->getByName('file_item_html_form_upload')->getParserValue());
        $this->assertSame('rendered_text_content', $tokenCollection->getByName('file_item_text_form_upload')->getParserValue());
    }
}
