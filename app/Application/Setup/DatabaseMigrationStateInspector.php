<?php

declare(strict_types=1);

namespace App\Application\Setup;

use Tempest\Database\Database;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Database\Query;
use Tempest\Database\QueryStatement;

/** Inspects Tempest migration state without registering EventBus listeners. */
final readonly class DatabaseMigrationStateInspector
{
    private const string VALIDATION_ERROR_FORMAT = '%s: %s';
    private const string MIGRATION_FILE_MISSING = 'migration file missing';
    private const string HASH_MISMATCH = 'hash mismatch';
    private const string MIGRATION_HASH_ALGORITHM = 'xxh128';
    private const string SQL_LINE_COMMENT_PATTERN = '/--.*$/m';
    private const string SQL_BLOCK_COMMENT_PATTERN = '/\/\*[\s\S]*?\*\//';
    private const string SQL_WHITESPACE_PATTERN = '/\s+/';

    public function __construct(
        private RunnableMigrations $migrations,
        private Database $database,
    ) {}

    public function status(): DatabaseUpdateStatus
    {
        return $this->statusWithValidationErrors([]);
    }

    public function validatedStatus(): DatabaseUpdateStatus
    {
        return $this->statusWithValidationErrors($this->validationErrors());
    }

    /** @param string[] $validationErrors */
    private function statusWithValidationErrors(array $validationErrors): DatabaseUpdateStatus
    {
        [$executedMigrationNames, $migrationTableMissing] = $this->executedMigrationNames();

        return new DatabaseUpdateStatus(
            pendingMigrations: $this->pendingMigrationNames($executedMigrationNames),
            validationErrors: $validationErrors,
            migrationTableMissing: $migrationTableMissing,
        );
    }

    /** @return array{0: array<string, true>, 1: bool} */
    private function executedMigrationNames(): array
    {
        try {
            $migrations = Migration::select()->all();
        } catch (QueryWasInvalid) {
            return [[], true];
        }

        $names = [];
        foreach ($migrations as $migration) {
            $names[$migration->name] = true;
        }

        return [$names, false];
    }

    /** @param array<string, true> $executedMigrationNames @return string[] */
    private function pendingMigrationNames(array $executedMigrationNames): array
    {
        $pending = [];

        foreach ($this->migrations->up() as $migration) {
            if (! isset($executedMigrationNames[$migration->name])) {
                $pending[] = $migration->name;
            }
        }

        return $pending;
    }

    /** @return string[] */
    private function validationErrors(): array
    {
        $errors = [];
        $runnableMigrations = $this->runnableMigrationsByName();

        try {
            $executedMigrations = Migration::select()->all();
        } catch (QueryWasInvalid) {
            return [];
        }

        foreach ($executedMigrations as $executedMigration) {
            $runnableMigration = $runnableMigrations[$executedMigration->name] ?? null;

            if ($runnableMigration === null) {
                $errors[] = sprintf(self::VALIDATION_ERROR_FORMAT, $executedMigration->name, self::MIGRATION_FILE_MISSING);

                continue;
            }

            if ($this->migrationHash($runnableMigration) !== $executedMigration->hash) {
                $errors[] = sprintf(self::VALIDATION_ERROR_FORMAT, $executedMigration->name, self::HASH_MISMATCH);
            }
        }

        return array_values(array_unique($errors));
    }

    /** @return array<string, MigratesUp> */
    private function runnableMigrationsByName(): array
    {
        $migrations = [];

        foreach ($this->migrations->up() as $migration) {
            $migrations[$migration->name] = $migration;
        }

        return $migrations;
    }


    private function migrationHash(MigratesUp|MigratesDown $migration): string
    {
        $sql = '';

        if ($migration instanceof MigratesUp) {
            $sql .= $this->minifiedSqlFromStatement($migration->up());
        }

        if ($migration instanceof MigratesDown) {
            $sql .= $this->minifiedSqlFromStatement($migration->down());
        }

        return hash(algo: self::MIGRATION_HASH_ALGORITHM, data: $sql);
    }

    private function minifiedSqlFromStatement(?QueryStatement $statement): string
    {
        if ($statement === null) {
            return '';
        }

        $query = new Query($statement->compile($this->database->dialect));
        $sql = preg_replace(pattern: self::SQL_LINE_COMMENT_PATTERN, replacement: '', subject: $query->compile()->toString());
        $sql = preg_replace(pattern: self::SQL_BLOCK_COMMENT_PATTERN, replacement: '', subject: (string) $sql);
        $sql = preg_replace(pattern: self::SQL_WHITESPACE_PATTERN, replacement: ' ', subject: trim((string) $sql));

        return (string) $sql;
    }
}
