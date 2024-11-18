<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Gateway;

use Contao\Controller;
use Contao\CoreBundle\Filesystem\Dbafs\DbafsManager;
use Contao\CoreBundle\Filesystem\MountManager;
use Contao\CoreBundle\Filesystem\VirtualFilesystem;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\String\SimpleTokenParser;
use Contao\FrontendTemplate;
use Contao\TestCase\ContaoTestCase;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\RouterInterface;
use Terminal42\NotificationCenterBundle\BulkyItem\BulkyItemStorage;
use Terminal42\NotificationCenterBundle\Config\LanguageConfig;
use Terminal42\NotificationCenterBundle\Config\MessageConfig;
use Terminal42\NotificationCenterBundle\Gateway\AbstractGateway;
use Terminal42\NotificationCenterBundle\Gateway\MailerGateway;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\LanguageConfigStamp;
use Terminal42\NotificationCenterBundle\Parcel\Stamp\TokenCollectionStamp;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\InMemoryDbafs;
use Terminal42\NotificationCenterBundle\Test\BulkyItem\VirtualFilesystemCollection;
use Terminal42\NotificationCenterBundle\Token\Token;
use Terminal42\NotificationCenterBundle\Token\TokenCollection;

class MailerGatewayTest extends ContaoTestCase
{
    /**
     * @dataProvider embeddingHtmlImagesProvider
     *
     * @param array<string, string> $mockFiles
     * @param array<string, string> $expectedAttachmentsContentsAndPath
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
                    $attachments = [];

                    foreach ($email->getAttachments() as $attachment) {
                        $attachments[$attachment->getBody()] = $attachment->getName();
                    }

                    $expectedHtml = $parsedTemplateHtml;

                    foreach ($expectedAttachmentsContentsAndPath as $content => $path) {
                        $expectedHtml = str_replace($path, 'cid:'.$attachments[$content], $expectedHtml);
                    }

                    return $expectedHtml === $email->getHtmlBody();
                },
            ))
        ;

        $tokenCollection = new TokenCollection();
        $tokenCollection->addToken(Token::fromValue('admin_email', 'foobar@example.com'));
        $tokenCollection->addToken(Token::fromValue('recipient_email', 'foobar@example.com'));

        $parcel = new Parcel(MessageConfig::fromArray([
            'email_template' => 'mail_default',
        ]));
        $parcel = $parcel->withStamp(new LanguageConfigStamp(LanguageConfig::fromArray([
            'recipients' => '##recipient_email##',
            'email_mode' => 'textAndHtml',
        ])));
        $parcel = $parcel->withStamp(new TokenCollectionStamp($tokenCollection));

        $gateway = new MailerGateway(
            $this->createFrameWorkWithTemplate($parsedTemplateHtml),
            $vfsCollection->get('files'),
            $mailer,
        );
        $container = new Container();
        $container->set(AbstractGateway::SERVICE_NAME_BULKY_ITEM_STORAGE, new BulkyItemStorage($vfsCollection->get('bulky_item'), $this->createMock(RouterInterface::class), $this->createMock(UriSigner::class)));
        $container->set(AbstractGateway::SERVICE_NAME_SIMPLE_TOKEN_PARSER, new SimpleTokenParser(new ExpressionLanguage()));
        $gateway->setContainer($container);

        $parcel = $gateway->sealParcel($parcel);
        $gateway->sendParcel($parcel);
    }

    /**
     * @return iterable<array{0: string, 1: array<string, string>, 2: array<string, string>}>
     */
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

    private function createFrameWorkWithTemplate(string $parsedTemplateHtml): ContaoFramework
    {
        $controllerAdapter = $this->mockAdapter(['convertRelativeUrls']);
        $controllerAdapter
            ->method('convertRelativeUrls')
            ->willReturnCallback(static fn (string $template): string => $template)
        ;

        $templateInstance = $this->createMock(FrontendTemplate::class);
        $templateInstance
            ->expects($this->once())
            ->method('parse')
            ->willReturn($parsedTemplateHtml)
        ;

        return $this->mockContaoFramework(
            [
                Controller::class => $controllerAdapter,
            ],
            [
                FrontendTemplate::class => $templateInstance,
            ],
        );
    }
}
