<?php

declare(strict_types=1);

namespace App\Application\Setup;

/** Result object returned after an admin-triggered database update attempt. */
final readonly class DatabaseUpdateResult {
    /**
     * @param string[] $migratedMigrations
     * @param string[] $remainingPendingMigrations
     * @param string[] $validationErrors
     */
    public function __construct(
        public array $migratedMigrations = [],
        public array $remainingPendingMigrations = [],
        public array $validationErrors = [],
        public ?string $errorMessage = null,
    ) {}

    public function successful(): bool {
        return $this->errorMessage === null && $this->validationErrors === [];
    }
}
