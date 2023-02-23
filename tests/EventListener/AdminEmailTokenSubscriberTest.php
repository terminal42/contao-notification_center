<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Event\CreateParcelEvent;
use Terminal42\NotificationCenterBundle\EventListener\AdminEmailTokenSubscriber;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\CoreTokenDefinitionFactory;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class AdminEmailTokenSubscriberTest extends ContaoTestCase
{
    public function testAddsTokenFromPageModel(): void
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, [
            'adminEmail' => 'foobar@terminal42.ch',
        ]);

        $stack = $this->buildRequestStack($pageModel);
        $tokenCollection = new TokenCollection();

        $event = $this->buildCreateParcelEvent($tokenCollection);

        $tokenDefinitionFactory = new CoreTokenDefinitionFactory();

        $listener = new AdminEmailTokenSubscriber(
            $stack,
            $tokenDefinitionFactory,
            $this->mockFrameworkWithAdminEmail('foobar-config@terminal42.ch')
        );
        $listener->onCreateParcel($event);

        $this->assertSame('foobar@terminal42.ch', $tokenCollection->getByName('admin_email')->getValue());
    }

    public function testAddsTokenFromConfig(): void
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, [
            'adminEmail' => '',
        ]);

        $stack = $this->buildRequestStack($pageModel);
        $tokenCollection = new TokenCollection();

        $event = $this->buildCreateParcelEvent($tokenCollection);

        $tokenDefinitionFactory = new CoreTokenDefinitionFactory();

        $listener = new AdminEmailTokenSubscriber(
            $stack,
            $tokenDefinitionFactory,
            $this->mockFrameworkWithAdminEmail('foobar-config@terminal42.ch')
        );
        $listener->onCreateParcel($event);

        $this->assertSame('foobar-config@terminal42.ch', $tokenCollection->getByName('admin_email')->getValue());
    }

    private function buildRequestStack(PageModel $pageModel = null): RequestStack
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

    private function mockFrameworkWithAdminEmail(string $adminEmail = null): ContaoFramework
    {
        $adapter = $this->mockAdapter(['isComplete', 'get']);
        $adapter
            ->method('isComplete')
            ->willReturn(true)
        ;

        $adapter
            ->method('get')
            ->with('adminEmail')
            ->willReturn($adminEmail)
        ;

        return $this->mockContaoFramework([
            Config::class => $adapter,
        ]);
    }
}
