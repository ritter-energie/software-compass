<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Component\Component;
use App\Domain\Component\ComponentRepository;
use App\Domain\Component\ComponentSearchCriteria;
use App\Shared\ValueObject\Slug;
use DateTimeImmutable;

use function Tempest\Database\query;

/**
 * Tempest query-builder-backed implementation of {@see ComponentRepository}.
 *
 * This repository deliberately works with plain associative arrays (rather
 * than a dedicated ORM model class) via `query('components')`. Tempest's
 * query builder accepts a table name directly for this purpose, which
 * keeps the mapping between the {@see Component} domain entity and its
 * database representation explicit and in one place.
 */
final class MariaDbComponentRepository implements ComponentRepository
{
    use ResolvesLastInsertId;

    private const string TABLE = 'components';

    public function findById(int $id): ?Component
    {
        $row = query(self::TABLE)->select()->whereField('id', $id)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function findBySlug(string $slug): ?Component
    {
        $row = query(self::TABLE)->select()->whereField('slug', $slug)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function search(ComponentSearchCriteria $criteria): array
    {
        $builder = query(self::TABLE)->select();

        if ($criteria->query !== null && trim($criteria->query) !== '') {
            $like = '%' . $criteria->query . '%';
            $builder->whereGroup(function ($group) use ($like): void {
                $group
                    ->where('name', $like, 'LIKE')
                    ->orWhere('short_name', $like, 'LIKE')
                    ->orWhere('purpose', $like, 'LIKE')
                    ->orWhere('description', $like, 'LIKE')
                    ->orWhere('project_name', $like, 'LIKE')
                    ->orWhere('vendor', $like, 'LIKE');
            });
        }

        if ($criteria->componentTypeId !== null) {
            $builder->whereField('component_type_id', $criteria->componentTypeId);
        }

        if ($criteria->statusId !== null) {
            $builder->whereField('status_id', $criteria->statusId);
        }

        if ($criteria->criticalityId !== null) {
            $builder->whereField('criticality_id', $criteria->criticalityId);
        }

        if ($criteria->environmentId !== null) {
            $builder->whereField('environment_id', $criteria->environmentId);
        }

        if ($criteria->isExternal !== null) {
            $builder->whereField('is_external', $criteria->isExternal);
        }

        if ($criteria->ownerId !== null) {
            $builder->whereGroup(function ($group) use ($criteria): void {
                $group
                    ->where('business_owner_id', $criteria->ownerId)
                    ->orWhere('technical_owner_id', $criteria->ownerId);
            });
        }

        $rows = $builder->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function all(): array
    {
        $rows = query(self::TABLE)->select()->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function save(Component $component): Component
    {
        $data = $this->toRow($component);

        if ($component->id() === null) {
            query(self::TABLE)->insert($data)->execute();

            return $this->findById($this->lastInsertId());
        }

        query(self::TABLE)
            ->update(...$data)
            ->whereField('id', $component->id())
            ->execute();

        return $this->findById($component->id());
    }

    public function delete(int $id): void
    {
        query(self::TABLE)->delete()->whereField('id', $id)->execute();
    }

    public function slugExists(string $slug, ?int $excludingId = null): bool
    {
        $builder = query(self::TABLE)->select()->whereField('slug', $slug);

        if ($excludingId !== null) {
            $builder->andWhere('id', $excludingId, '!=');
        }

        return $builder->first() !== null;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toDomain(array $row): Component
    {
        return new Component(
            id: (int) $row['id'],
            name: $row['name'],
            shortName: $row['short_name'],
            slug: $row['slug'],
            componentTypeId: (int) $row['component_type_id'],
            statusId: (int) $row['status_id'],
            criticalityId: $row['criticality_id'] !== null ? (int) $row['criticality_id'] : null,
            businessOwnerId: $row['business_owner_id'] !== null ? (int) $row['business_owner_id'] : null,
            technicalOwnerId: $row['technical_owner_id'] !== null ? (int) $row['technical_owner_id'] : null,
            deploymentLocationId: $row['deployment_location_id'] !== null ? (int) $row['deployment_location_id'] : null,
            environmentId: $row['environment_id'] !== null ? (int) $row['environment_id'] : null,
            projectName: $row['project_name'],
            startedOn: $row['started_on'] !== null ? new DateTimeImmutable((string) $row['started_on']) : null,
            purpose: $row['purpose'],
            description: $row['description'],
            documentationUrl: $row['documentation_url'],
            repositoryUrl: $row['repository_url'],
            vendor: $row['vendor'],
            lifecycleNotes: $row['lifecycle_notes'],
            isExternal: (bool) $row['is_external'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toRow(Component $component): array
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        $row = [
            'name' => $component->name(),
            'short_name' => $component->shortName(),
            'slug' => $component->slug(),
            'component_type_id' => $component->componentTypeId(),
            'status_id' => $component->statusId(),
            'criticality_id' => $component->criticalityId(),
            'business_owner_id' => $component->businessOwnerId(),
            'technical_owner_id' => $component->technicalOwnerId(),
            'deployment_location_id' => $component->deploymentLocationId(),
            'environment_id' => $component->environmentId(),
            'project_name' => $component->projectName(),
            'started_on' => $component->startedOn()?->format('Y-m-d'),
            'purpose' => $component->purpose(),
            'description' => $component->description(),
            'documentation_url' => $component->documentationUrl(),
            'repository_url' => $component->repositoryUrl(),
            'vendor' => $component->vendor(),
            'lifecycle_notes' => $component->lifecycleNotes(),
            'is_external' => $component->isExternal(),
            'updated_at' => $now,
        ];

        if ($component->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }
}

