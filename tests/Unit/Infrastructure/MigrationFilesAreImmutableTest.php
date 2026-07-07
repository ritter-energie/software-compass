<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MigrationFilesAreImmutableTest extends TestCase
{
    public function test_migration_files_match_the_tracked_hash_manifest(): void
    {
        $root = dirname(__DIR__, 3);
        $manifestPath = $root . '/database/migrations/.migration-hashes.json';

        self::assertFileExists($manifestPath, 'The migration hash manifest is missing.');

        /** @var array<string, string> $expected */
        $expected = json_decode((string) file_get_contents($manifestPath), associative: true, flags: JSON_THROW_ON_ERROR);
        $actual = $this->migrationHashes($root . '/database/migrations');

        ksort($expected);
        ksort($actual);

        self::assertSame(
            [],
            array_keys(array_diff_key($expected, $actual)),
            'A tracked migration file was removed. Migrations are immutable; add a new forward migration instead.',
        );

        self::assertSame(
            [],
            array_keys(array_diff_key($actual, $expected)),
            'A new migration file was added but not recorded in database/migrations/.migration-hashes.json. If this is intentional, update the manifest in the same commit.',
        );

        $changed = [];
        foreach ($expected as $file => $hash) {
            if (($actual[$file] ?? null) !== $hash) {
                $changed[] = $file;
            }
        }

        self::assertSame(
            [],
            $changed,
            'Existing migration files were modified. Do not edit applied migrations; add a new migration that changes the schema forward.',
        );
    }

    /** @return array<string, string> */
    private function migrationHashes(string $migrationDirectory): array
    {
        $files = glob($migrationDirectory . '/*.php');

        if ($files === false) {
            throw new RuntimeException('Unable to read migration directory.');
        }

        $hashes = [];
        foreach ($files as $file) {
            $hashes[basename($file)] = (string) hash_file('sha256', $file);
        }

        return $hashes;
    }
}
