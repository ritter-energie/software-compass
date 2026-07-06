<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use DateTimeImmutable;

use function Tempest\Database\query;

/**
 * Lightweight access to lookup/master-data tables. This intentionally stays
 * in Infrastructure because these are database-backed read models rather than
 * rich domain entities.
 */
final class LookupRepository
{
    use ResolvesLastInsertId;

    /** @return array<int, array<string, mixed>> */
    public function componentTypes(): array
    {
        return $this->all('component_types');
    }

    /** @return array<int, array<string, mixed>> */
    public function componentStatuses(): array
    {
        return $this->all('component_statuses', 'sort_order');
    }

    /** @return array<int, array<string, mixed>> */
    public function criticalityLevels(): array
    {
        return $this->all('criticality_levels', 'sort_order');
    }

    /** @return array<int, array<string, mixed>> */
    public function environments(): array
    {
        return $this->all('environments');
    }

    /** @return array<int, array<string, mixed>> */
    public function deploymentLocations(): array
    {
        return $this->all('deployment_locations');
    }

    /** @return array<int, array<string, mixed>> */
    public function dependencyTypes(): array
    {
        return $this->all('dependency_types');
    }

    /** @return array<int, array<string, mixed>> */
    public function communicationProtocols(): array
    {
        return $this->all('communication_protocols');
    }

    /** @return array<int, array<string, mixed>> */
    public function dataObjects(): array
    {
        return $this->all('data_objects');
    }

    /** @return array<int, array<string, mixed>> */
    public function tags(): array
    {
        return $this->all('tags');
    }

    /** @return array<int, array<string, mixed>> */
    public function teams(): array
    {
        return $this->all('teams');
    }

    /** @return array<int, array<string, mixed>> */
    public function allFrom(string $table, string $orderBy = 'name'): array
    {
        return $this->all($table, $orderBy);
    }

    /** @return array<string, mixed>|null */
    public function findIn(string $table, int $id): ?array
    {
        return query($table)->select()->whereField('id', $id)->first();
    }

    public function idByName(string $table, string $name): ?int
    {
        $row = query($table)->select()->whereField('name', $name)->first();

        return $row ? (int) $row['id'] : null;
    }

    /** @param array<string, mixed> $values */
    public function upsertByName(string $table, string $name, array $values = []): int
    {
        $existing = $this->idByName($table, $name);
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        if ($existing !== null) {
            query($table)->update(...[...$values, 'updated_at' => $now])->whereField('id', $existing)->execute();

            return $existing;
        }

        $id = query($table)->insert([
            'name' => $name,
            ...$values,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return $id?->value !== null ? (int) $id->value : $this->lastInsertId();
    }

    /** @param array<string, mixed> $values */
    public function insertInto(string $table, array $values): int
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $id = query($table)->insert([
            ...$values,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return $id?->value !== null ? (int) $id->value : $this->lastInsertId();
    }

    /** @param array<string, mixed> $values */
    public function updateIn(string $table, int $id, array $values): void
    {
        query($table)
            ->update(...[
                ...$values,
                'updated_at' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
            ])
            ->whereField('id', $id)
            ->execute();
    }

    public function deleteFrom(string $table, int $id): void
    {
        query($table)->delete()->whereField('id', $id)->execute();
    }

    /** @return array<int, array<string, mixed>> */
    private function all(string $table, string $orderBy = 'name'): array
    {
        return query($table)->select()->orderBy($orderBy)->all();
    }
}
