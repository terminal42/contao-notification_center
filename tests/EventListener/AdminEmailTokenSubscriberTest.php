<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\EventListener;

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
    public function testAddsToken(): void
    {
        $pageModel = $this->mockClassWithProperties(PageModel::class, [
            'adminEmail' => 'foobar@terminal42.ch',
        ]);

        $request = new Request();
        $request->attributes->set('pageModel', $pageModel);
        $stack = new RequestStack();
        $stack->push($request);
        $tokenCollection = new TokenCollection();

        $parcel = new Parcel(MessageConfig::fromArray([]));
        $parcel = $parcel->withStamp(new TokenCollectionStamp($tokenCollection));
        $event = new CreateParcelEvent($parcel);

        $tokenDefinitionFactory = new CoreTokenDefinitionFactory();

        $listener = new AdminEmailTokenSubscriber($stack, $tokenDefinitionFactory);
        $listener->onCreateParcel($event);

        $this->assertSame('foobar@terminal42.ch', $tokenCollection->getByName('admin_email')->getValue());
    }
}
