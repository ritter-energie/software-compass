<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Dependency\Dependency;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Dependency\DependencySearchCriteria;
use DateTimeImmutable;

use function Tempest\Database\query;

/**
 * MariaDB persistence for documented interfaces/dependencies between
 * components. All filtering is done through Tempest's query builder so user
 * input is parameterized and never concatenated into SQL directly.
 */
final class MariaDbDependencyRepository implements DependencyRepository
{
    use ResolvesLastInsertId;

    private const string TABLE = 'dependencies';

    public function findById(int $id): ?Dependency
    {
        $row = query(self::TABLE)->select()->whereField('id', $id)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function findByComponentId(int $componentId): array
    {
        $rows = query(self::TABLE)
            ->select()
            ->whereGroup(static function ($group) use ($componentId): void {
                $group
                    ->where('source_component_id', $componentId)
                    ->orWhere('target_component_id', $componentId);
            })
            ->orderBy('name')
            ->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function findIncomingForComponent(int $componentId): array
    {
        $rows = query(self::TABLE)
            ->select()
            ->whereField('target_component_id', $componentId)
            ->orderBy('name')
            ->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function findOutgoingForComponent(int $componentId): array
    {
        $rows = query(self::TABLE)
            ->select()
            ->whereField('source_component_id', $componentId)
            ->orderBy('name')
            ->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function search(DependencySearchCriteria $criteria): array
    {
        $builder = query(self::TABLE)->select();

        if ($criteria->query !== null && trim($criteria->query) !== '') {
            $like = '%' . $criteria->query . '%';
            $builder->whereGroup(static function ($group) use ($like): void {
                $group
                    ->where('name', $like, 'LIKE')
                    ->orWhere('description', $like, 'LIKE')
                    ->orWhere('data_description', $like, 'LIKE')
                    ->orWhere('technical_notes', $like, 'LIKE');
            });
        }

        foreach ([
            'source_component_id' => $criteria->sourceComponentId,
            'target_component_id' => $criteria->targetComponentId,
            'dependency_type_id' => $criteria->dependencyTypeId,
            'protocol_id' => $criteria->protocolId,
            'status_id' => $criteria->statusId,
            'criticality_id' => $criteria->criticalityId,
            'owner_id' => $criteria->ownerId,
        ] as $field => $value) {
            if ($value !== null) {
                $builder->whereField($field, $value);
            }
        }

        if ($criteria->dataObjectId !== null) {
            $builder
                ->join('INNER JOIN dependency_data_objects ddo ON ddo.dependency_id = dependencies.id')
                ->whereField('ddo.data_object_id', $criteria->dataObjectId);
        }

        $rows = $builder->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function all(): array
    {
        $rows = query(self::TABLE)->select()->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function save(Dependency $dependency): Dependency
    {
        $data = $this->toRow($dependency);

        if ($dependency->id() === null) {
            query(self::TABLE)->insert($data)->execute();

            return $this->findById($this->lastInsertId());
        }

        query(self::TABLE)
            ->update(...$data)
            ->whereField('id', $dependency->id())
            ->execute();

        return $this->findById($dependency->id());
    }

    public function delete(int $id): void
    {
        query(self::TABLE)->delete()->whereField('id', $id)->execute();
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Dependency
    {
        return new Dependency(
            id: (int) $row['id'],
            sourceComponentId: (int) $row['source_component_id'],
            targetComponentId: (int) $row['target_component_id'],
            dependencyTypeId: (int) $row['dependency_type_id'],
            protocolId: $row['protocol_id'] !== null ? (int) $row['protocol_id'] : null,
            statusId: (int) $row['status_id'],
            criticalityId: $row['criticality_id'] !== null ? (int) $row['criticality_id'] : null,
            ownerId: $row['owner_id'] !== null ? (int) $row['owner_id'] : null,
            name: $row['name'],
            description: $row['description'],
            dataDescription: $row['data_description'],
            frequency: $row['frequency'],
            direction: $row['direction'],
            authenticationMethod: $row['authentication_method'],
            documentationUrl: $row['documentation_url'],
            technicalNotes: $row['technical_notes'],
            isBidirectional: (bool) $row['is_bidirectional'],
        );
    }

    /** @return array<string, mixed> */
    private function toRow(Dependency $dependency): array
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        $row = [
            'source_component_id' => $dependency->sourceComponentId(),
            'target_component_id' => $dependency->targetComponentId(),
            'dependency_type_id' => $dependency->dependencyTypeId(),
            'protocol_id' => $dependency->protocolId(),
            'status_id' => $dependency->statusId(),
            'criticality_id' => $dependency->criticalityId(),
            'owner_id' => $dependency->ownerId(),
            'name' => $dependency->name(),
            'description' => $dependency->description(),
            'data_description' => $dependency->dataDescription(),
            'frequency' => $dependency->frequency(),
            'direction' => $dependency->direction(),
            'authentication_method' => $dependency->authenticationMethod(),
            'documentation_url' => $dependency->documentationUrl(),
            'technical_notes' => $dependency->technicalNotes(),
            'is_bidirectional' => $dependency->isBidirectional(),
            'updated_at' => $now,
        ];

        if ($dependency->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }
}

