<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Gateway;

use Contao\CoreBundle\Filesystem\Dbafs\DbafsManager;
use Contao\CoreBundle\Filesystem\MountManager;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\TestCase\ContaoTestCase;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Gateway\AbstractGateway;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\InMemoryDbafs;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\VirtualFilesystemCollection;

class MailerGatewayTest extends ContaoTestCase
{
    /**
     * @dataProvider embeddingHtmlImagesProvider
     */
    public function testEmbeddingHtmlImages(string $parsedTemplateHtml, array $mockFiles, array $expectedAttachmentsContentsAndPath): void
    {
        $vfsCollection = $this->createVfsCollection();

        foreach ($mockFiles as $path => $contents) {
            $vfsCollection->get('files')->write($path, $contents);
        }

        $mailer = $this->createMock(MailerInterface::class);
        $mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                static function (Email $email) use ($parsedTemplateHtml, $expectedAttachmentsContentsAndPath): bool {
                    $attachements = [];

                    foreach ($email->getAttachments() as $attachment) {
                        $attachements[$attachment->getBody()] = $attachment->getName();
                    }

                    $expectedHtml = $parsedTemplateHtml;

                    foreach ($expectedAttachmentsContentsAndPath as $content => $path) {
                        $expectedHtml = str_replace($path, 'cid:'.$attachements[$content], $expectedHtml);
                    }

                    return $expectedHtml === $email->getHtmlBody();
                },
            ))
        ;

        $parcel = new Parcel(MessageConfig::fromArray([
            'email_template' => 'mail_default',
        ]));
        $parcel = $parcel->withStamp(new LanguageConfigStamp(LanguageConfig::fromArray([
            'email_mode' => 'textAndHtml',
        ])));

        $gateway = new MailerGateway(
            $this->createFrameWorkWithTemplate('mail_default', $parsedTemplateHtml),
            $vfsCollection->get('files'),
            $vfsCollection->get(''),
            $mailer,
        );
        $container = new Container();
        $container->set(AbstractGateway::SERVICE_NAME_BULKY_ITEM_STORAGE, new BulkyItemStorage($vfsCollection->get('bulky_item')));
        $gateway->setContainer($container);

        $parcel = $gateway->sealParcel($parcel);
        $gateway->sendParcel($parcel);
    }

    public static function embeddingHtmlImagesProvider(): iterable
    {
        yield 'Test embeds a relative upload path' => [
            '<html><body><p><img src="files/contaodemo/media/content-images/DSC_5276.jpg" alt="" width="800" height="533"></p></body></html>',
            [
                'contaodemo/media/content-images/DSC_5276.jpg' => 'foobar',
            ],
            [
                'foobar' => 'files/contaodemo/media/content-images/DSC_5276.jpg',
            ],
        ];

        yield 'Test embeds an absolute upload path' => [
            '<html><body><p><img src="/files/contaodemo/media/content-images/DSC_5276.jpg" alt="" width="800" height="533"></p></body></html>',
            [
                'contaodemo/media/content-images/DSC_5276.jpg' => 'foobar',
            ],
            [
                'foobar' => '/files/contaodemo/media/content-images/DSC_5276.jpg',
            ],
        ];
    }

    private function createVfsCollection(): VirtualFilesystemCollection
    {
        $mountManager = (new MountManager())
            ->mount(new InMemoryFilesystemAdapter(), 'files')
            ->mount(new InMemoryFilesystemAdapter(), 'bulky_item')
        ;

        $dbafsManager = new DbafsManager();
        $dbafsManager->register(new InMemoryDbafs(), 'files');
        $dbafsManager->register(new InMemoryDbafs(), 'bulky_item');

        $vfsCollection = new VirtualFilesystemCollection();
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, 'files'));
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, 'bulky_item'));
        $vfsCollection->add(new VirtualFilesystem($mountManager, $dbafsManager, '')); // Global one

        return $vfsCollection;
    }

    private function createFrameWorkWithTemplate(string $templateName, string $parsedTemplateHtml): ContaoFramework
    {
        $template = $this->createMock(FrontendTemplate::class);
        $template
            ->expects($this->once())
            ->method('parse')
            ->willReturn($parsedTemplateHtml)
        ;

        $framework = $this->mockContaoFramework();
        $framework
            ->expects($this->once())
            ->method('createInstance')
            ->with(FrontendTemplate::class, [$templateName])
            ->willReturn($template)
        ;

        return $framework;
    }
}
