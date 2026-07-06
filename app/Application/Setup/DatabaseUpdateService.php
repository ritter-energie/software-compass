<?php

declare(strict_types=1);

namespace App\Application\Setup;

use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Migrations\Migration;
use Tempest\Database\Migrations\MigrationFileWasMissing;
use Tempest\Database\Migrations\MigrationHashMismatched;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Migrations\MigrationMigrated;
use Tempest\Database\Migrations\MigrationValidationFailed;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\EventBus\EventBus;
use Throwable;

/** Detects pending database migrations and runs them when an administrator confirms the update. */
final readonly class DatabaseUpdateService
{
    public function __construct(
        private RunnableMigrations $migrations,
        private MigrationManager $migrationManager,
        private EventBus $eventBus,
    ) {}

    public function status(bool $validate = false): DatabaseUpdateStatus
    {
        [$executedMigrationNames, $migrationTableMissing] = $this->executedMigrationNames();

        return new DatabaseUpdateStatus(
            pendingMigrations: $this->pendingMigrationNames($executedMigrationNames),
            validationErrors: $validate ? $this->validationErrors() : [],
            migrationTableMissing: $migrationTableMissing,
        );
    }

    public function runPendingMigrations(): DatabaseUpdateResult
    {
        $status = $this->status(validate: true);

        if ($status->hasValidationErrors()) {
            return new DatabaseUpdateResult(
                remainingPendingMigrations: $status->pendingMigrations,
                validationErrors: $status->validationErrors,
            );
        }

        if (! $status->hasPendingMigrations()) {
            return new DatabaseUpdateResult();
        }

        $migratedMigrations = [];
        $this->eventBus->listen(
            static function (MigrationMigrated $event) use (&$migratedMigrations): void {
                $migratedMigrations[] = $event->name;
            },
            MigrationMigrated::class,
        );

        try {
            $this->migrationManager->up();
        } catch (Throwable $throwable) {
            return new DatabaseUpdateResult(
                migratedMigrations: $migratedMigrations,
                remainingPendingMigrations: $this->status()->pendingMigrations,
                errorMessage: $throwable->getMessage(),
            );
        }

        return new DatabaseUpdateResult(
            migratedMigrations: $migratedMigrations,
            remainingPendingMigrations: $this->status()->pendingMigrations,
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

        $this->eventBus->listen(
            function (MigrationValidationFailed $event) use (&$errors): void {
                $errors[] = sprintf('%s: %s', $event->name, $this->validationErrorLabel($event));
            },
            MigrationValidationFailed::class,
        );

        $this->migrationManager->validate();

        return array_values(array_unique($errors));
    }

    private function validationErrorLabel(MigrationValidationFailed $event): string
    {
        return match ($event->exception::class) {
            MigrationHashMismatched::class => 'hash mismatch',
            MigrationFileWasMissing::class => 'migration file missing',
            default => 'migration validation failed',
        };
    }
}
