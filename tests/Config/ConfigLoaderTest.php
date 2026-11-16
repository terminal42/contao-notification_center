<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Test\Config;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Terminal42\NotificationCenterBundle\Config\ConfigLoader;

final class ConfigLoaderTest extends TestCase
{
    /**
     * @param list<array{string, int}> $languages
     */
    #[DataProvider('provideLanguages')]
    public function testLoadLanguageForMessageAndLocale(array $languages, string|null $locale, int|null $expectedId): void
    {
        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $connection->executeStatement('CREATE TABLE tl_nc_language (id INTEGER PRIMARY KEY, pid INTEGER, language VARCHAR(8), fallback BOOLEAN)');

        foreach ($languages as $index => [$language, $fallback]) {
            $connection->insert('tl_nc_language', ['id' => $index + 1, 'pid' => 42, 'language' => $language, 'fallback' => $fallback]);
        }

        $connection->insert('tl_nc_language', ['id' => 100, 'pid' => 99, 'language' => 'fr', 'fallback' => 1]);
        $loader = new ConfigLoader($connection);

        $this->assertSame($expectedId, $loader->loadLanguageForMessageAndLocale(42, $locale)?->getId());
    }

    /**
     * @return iterable<string, array{list<array{string, int}>, string|null, int|null}>
     */
    public static function provideLanguages(): iterable
    {
        yield 'no languages' => [[], null, null];
        yield 'no languages for requested locale' => [[], 'de', null];
        yield 'sole language without locale' => [[['en', 0]], null, 1];
        yield 'sole language with unmatched locale' => [[['en', 0]], 'de', 1];
        yield 'sole matching language' => [[['en', 0]], 'en', 1];
        yield 'sole explicit fallback' => [[['en', 1]], null, 1];
        yield 'ambiguous without locale' => [[['en', 0], ['de', 0]], null, null];
        yield 'ambiguous with unmatched locale' => [[['en', 0], ['de', 0]], 'fr', null];
        yield 'explicit fallback without locale' => [[['en', 0], ['de', 1]], null, 2];
        yield 'explicit fallback with unmatched locale' => [[['en', 0], ['de', 1]], 'fr', 2];
        yield 'matching language before fallback' => [[['en', 0], ['de', 1]], 'en', 1];
        yield 'exact region before base language' => [[['en', 0], ['en_US', 0], ['de', 1]], 'en_US', 2];
        yield 'base language before fallback' => [[['en', 0], ['de', 1]], 'en_GB', 1];
    }
}
