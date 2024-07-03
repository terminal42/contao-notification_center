<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\EventListener\AdminEmailTokenListener;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\CoreTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class AdminEmailTokenListenerTest extends ContaoTestCase
{
    #[DataProvider('adminEmailProvider')]
    public function testAddsAdminTokens(string $configFriendlyEmail, string $pageFriendlyEmail, string $expectedName, string $expectedEmail): void
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, [
            'adminEmail' => $pageFriendlyEmail,
        ]);

        $stack = $this->buildRequestStack($pageModel);
        $tokenCollection = new TokenCollection();

        $event = $this->buildCreateParcelEvent($tokenCollection);

        $tokenDefinitionFactory = new CoreTokenDefinitionFactory();

        $listener = new AdminEmailTokenListener(
            $stack,
            $tokenDefinitionFactory,
            $this->mockFrameworkWithAdminEmail($configFriendlyEmail),
        );
        $listener->onCreateParcel($event);

        $this->assertSame($expectedName, $tokenCollection->getByName('admin_name')->getValue());
        $this->assertSame($expectedEmail, $tokenCollection->getByName('admin_email')->getValue());
    }

    public static function adminEmailProvider(): \Generator
    {
        yield 'Basic admin email in config' => [
            'foobar-config@terminal42.ch',
            '',
            '',
            'foobar-config@terminal42.ch',
        ];

        yield 'Friendly admin email in config' => [
            'Lorem Ipsum [foobar-config@terminal42.ch]',
            '',
            'Lorem Ipsum',
            'foobar-config@terminal42.ch',
        ];

        yield 'Basic admin email in page' => [
            'Lorem Ipsum [foobar-config@terminal42.ch]',
            'foobar@terminal42.ch',
            '',
            'foobar@terminal42.ch',
        ];

        yield 'Friendly admin email in page' => [
            'Lorem Ipsum [foobar-config@terminal42.ch]',
            'Dolor Sitamet [foobar@terminal42.ch]',
            'Dolor Sitamet',
            'foobar@terminal42.ch',
        ];
    }

    private function buildRequestStack(PageModel|null $pageModel = null): RequestStack
    {
        $request = new Request();
        $request->attributes->set('pageModel', $pageModel);
        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }

    private function buildCreateParcelEvent(TokenCollection $tokenCollection): CreateParcelEvent
    {
        $parcel = new Parcel(MessageConfig::fromArray([]));
        $parcel = $parcel->withStamp(new TokenCollectionStamp($tokenCollection));

        return new CreateParcelEvent($parcel);
    }

    private function mockFrameworkWithAdminEmail(string|null $adminEmail = null): ContaoFramework
    {
        $configAdapter = $this->mockAdapter(['isComplete', 'get']);
        $configAdapter
            ->method('isComplete')
            ->willReturn(true)
        ;

        $configAdapter
            ->method('get')
            ->with('adminEmail')
            ->willReturn($adminEmail)
        ;

        $stringUtilAdapter = $this->mockAdapter(['splitFriendlyEmail']);
        $stringUtilAdapter
            ->method('splitFriendlyEmail')
            ->willReturnCallback(
                static fn (string $email): array => match ($email) {
                    'Lorem Ipsum [foobar-config@terminal42.ch]' => ['Lorem Ipsum', 'foobar-config@terminal42.ch'],
                    'Dolor Sitamet [foobar@terminal42.ch]' => ['Dolor Sitamet', 'foobar@terminal42.ch'],
                    default => ['', $email],
                },
            )
        ;

        return $this->mockContaoFramework([
            Config::class => $configAdapter,
            // StringUtil::class => $stringUtilAdapter,
        ]);
    }
}
