<?php

declare(strict_types=1);

namespace App\Application\Setup;

/** Describes the current database migration state for the admin UI. */
final readonly class DatabaseUpdateStatus {
    /**
     * @param string[] $pendingMigrations
     * @param string[] $validationErrors
     */
    public function __construct(
        public array $pendingMigrations,
        public array $validationErrors = [],
        public bool $migrationTableMissing = false,
    ) {}

    public function hasPendingMigrations(): bool {
        return $this->pendingMigrations !== [];
    }

    public function hasValidationErrors(): bool {
        return $this->validationErrors !== [];
    }
}
