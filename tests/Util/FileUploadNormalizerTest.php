<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Util;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Terminal42\NotificationCenterBundle\Util\FileUploadNormalizer;

class FileUploadNormalizerTest extends TestCase
{
    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(array $input, array $expected, string $projectDir, MimeTypeGuesserInterface $mimeTypeGuesser, VirtualFilesystemInterface $virtualFilesystem): void
    {
        $normalizer = new FileUploadNormalizer($projectDir, $mimeTypeGuesser, $virtualFilesystem);
        $normalized = $normalizer->normalize($input);

        foreach ($normalized as $k => $files) {
            foreach ($files as $kk => $file) {
                $this->assertArrayHasKey('stream', $file);
                unset($normalized[$k][$kk]['stream']);
            }
        }
        $this->assertSame($expected, $normalized);
    }

    public function normalizeProvider(): \Generator
    {
        yield 'Already in correct format' => [
            [
                'upload_field' => [
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ],
            ],
            [
                'upload_field' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ]],
            ],
            '/project-dir',
            $this->mockMimeTypeGuesserThatIsNeverCalled(),
            $this->mockFilesystemThatIsNeverCalled(),
        ];

        yield 'Single UUID' => [
            [
                'upload_field' => '660d272c-f4c3-11ed-a05b-0242ac120003',
            ],
            [
                'upload_field' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => '660d272c-f4c3-11ed-a05b-0242ac120003',
                ]],
            ],
            '/project-dir',
            $this->mockMimeTypeGuesserThatIsNeverCalled(),
            $this->mockFilesystemThatReturnsAFilesystemItem(new FilesystemItem(
                true,
                'path/to/name.jpg',
                null,
                333,
                'image/jpeg',
            )),
        ];

        yield 'Single file path' => [
            [
                'upload_field' => __DIR__.'/../Fixtures/name.jpg',
            ],
            [
                'upload_field' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => Path::makeAbsolute('../Fixtures/name.jpg', __DIR__),
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ]],
            ],
            '/project-dir',
            $this->mockMimeTypeGuesserThatReturnsAType('image/jpeg'),
            $this->mockFilesystemThatIsNeverCalled(),
        ];

        yield 'Array of it all' => [
            [
                'upload_field_already_correct' => [
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ],
                'upload_field_uuid' => '660d272c-f4c3-11ed-a05b-0242ac120003',
                'upload_field_path' => __DIR__.'/../Fixtures/name.jpg',
                'upload_multiple' => [
                    [
                        'name' => 'name.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => 'path/to/name.jpg',
                        'error' => 0,
                        'size' => 333,
                        'uploaded' => true,
                        'uuid' => null,
                    ],
                    '660d272c-f4c3-11ed-a05b-0242ac120003',
                    __DIR__.'/../Fixtures/name.jpg',
                ],
            ],
            [
                'upload_field_already_correct' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ]],
                'upload_field_uuid' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => 'path/to/name.jpg',
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => '660d272c-f4c3-11ed-a05b-0242ac120003',
                ]],
                'upload_field_path' => [[
                    'name' => 'name.jpg',
                    'type' => 'image/jpeg',
                    'tmp_name' => Path::makeAbsolute('../Fixtures/name.jpg', __DIR__),
                    'error' => 0,
                    'size' => 333,
                    'uploaded' => true,
                    'uuid' => null,
                ]],
                'upload_multiple' => [
                    [
                        'name' => 'name.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => 'path/to/name.jpg',
                        'error' => 0,
                        'size' => 333,
                        'uploaded' => true,
                        'uuid' => null,
                    ], [
                        'name' => 'name.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => 'path/to/name.jpg',
                        'error' => 0,
                        'size' => 333,
                        'uploaded' => true,
                        'uuid' => '660d272c-f4c3-11ed-a05b-0242ac120003',
                    ], [
                        'name' => 'name.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => Path::makeAbsolute('../Fixtures/name.jpg', __DIR__),
                        'error' => 0,
                        'size' => 333,
                        'uploaded' => true,
                        'uuid' => null,
                    ],
                ],
            ],
            '/project-dir',
            $this->mockMimeTypeGuesserThatReturnsAType('image/jpeg'),
            $this->mockFilesystemThatReturnsAFilesystemItem(new FilesystemItem(
                true,
                'path/to/name.jpg',
                null,
                333,
                'image/jpeg',
            )),
        ];
    }

    private function mockMimeTypeGuesserThatIsNeverCalled(): MimeTypeGuesserInterface
    {
        $mock = $this->createMock(MimeTypeGuesserInterface::class);
        $mock
            ->expects($this->never())
            ->method('guessMimeType')
        ;

        return $mock;
    }

    private function mockMimeTypeGuesserThatReturnsAType(string $type): MimeTypeGuesserInterface
    {
        $mock = $this->createMock(MimeTypeGuesserInterface::class);
        $mock
            ->expects($this->atLeastOnce())
            ->method('guessMimeType')
            ->willReturn($type)
        ;

        return $mock;
    }

    private function mockFilesystemThatIsNeverCalled(): VirtualFilesystemInterface
    {
        $mock = $this->createMock(VirtualFilesystemInterface::class);
        $mock
            ->expects($this->never())
            ->method('get')
        ;

        return $mock;
    }

    private function mockFilesystemThatReturnsAFilesystemItem(FilesystemItem $item): VirtualFilesystemInterface
    {
        $mock = $this->createMock(VirtualFilesystemInterface::class);
        $mock
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($item)
        ;

        return $mock;
    }
}
