<?php

declare(strict_types=1);

namespace App\Application\Audit;

use App\Infrastructure\Security\CurrentUser;
use DateTimeImmutable;
use JsonException;

use function Tempest\Database\query;

/** Writes audit trail entries for mutable architecture repository entities. */
final readonly class AuditLogger {
    /** @param array<string, mixed>|null $oldValues @param array<string, mixed>|null $newValues */
    public function log(string $entityType, int $entityId, string $action, ?array $oldValues = null, ?array $newValues = null): void {
        query('audit_logs')->insert([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $this->jsonOrNull($oldValues),
            'new_values' => $this->jsonOrNull($newValues),
            'changed_by' => CurrentUser::personId(),
            'created_at' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
        ])->execute();
    }

    /** @param array<string, mixed>|null $values */
    private function jsonOrNull(?array $values): ?string {
        if ($values === null) {
            return null;
        }

        try {
            return json_encode($values, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return json_encode(['serialization_error' => true]);
        }
    }
}
