<?php

declare(strict_types=1);

namespace App\Application\Setup;

use Tempest\Database\Migrations\MigrationManager;
use Throwable;

/** Detects pending database migrations and runs them when an administrator confirms the update. */
final readonly class DatabaseUpdateService {
    public function __construct(
        private DatabaseMigrationStateInspector $migrationState,
        private MigrationManager $migrationManager,
    ) {}

    public function status(): DatabaseUpdateStatus {
        return $this->migrationState->status();
    }

    public function validatedStatus(): DatabaseUpdateStatus {
        return $this->migrationState->validatedStatus();
    }

    public function runPendingMigrations(): DatabaseUpdateResult {
        $status = $this->validatedStatus();

        if ($status->hasValidationErrors()) {
            return new DatabaseUpdateResult(
                remainingPendingMigrations: $status->pendingMigrations,
                validationErrors: $status->validationErrors,
            );
        }

        if (! $status->hasPendingMigrations()) {
            return new DatabaseUpdateResult();
        }

        $pendingBeforeUpdate = $status->pendingMigrations;

        try {
            $this->migrationManager->up();
        } catch (Throwable $throwable) {
            $remainingPendingMigrations = $this->status()->pendingMigrations;

            return new DatabaseUpdateResult(
                migratedMigrations: $this->migratedMigrationNames($pendingBeforeUpdate, $remainingPendingMigrations),
                remainingPendingMigrations: $remainingPendingMigrations,
                errorMessage: $throwable->getMessage(),
            );
        }

        $remainingPendingMigrations = $this->status()->pendingMigrations;

        return new DatabaseUpdateResult(
            migratedMigrations: $this->migratedMigrationNames($pendingBeforeUpdate, $remainingPendingMigrations),
            remainingPendingMigrations: $remainingPendingMigrations,
        );
    }

    /** @param string[] $pendingBeforeUpdate @param string[] $remainingPendingMigrations @return string[] */
    private function migratedMigrationNames(array $pendingBeforeUpdate, array $remainingPendingMigrations): array {
        return array_values(array_diff($pendingBeforeUpdate, $remainingPendingMigrations));
    }
}
