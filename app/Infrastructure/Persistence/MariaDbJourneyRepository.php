<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Journey\Journey;
use App\Domain\Journey\JourneyRepository;
use App\Domain\Journey\JourneyStep;
use App\Domain\Journey\JourneyStepComponent;
use DateTimeImmutable;
use Tempest\Database\Builder\WhereOperator;

use function Tempest\Database\query;

/**
 * Query-builder-backed repository for Customer Journeys, their ordered steps
 * and component assignments.
 */
final class MariaDbJourneyRepository implements JourneyRepository
{
    use ResolvesLastInsertId;

    public function findById(int $id): ?Journey
    {
        $row = query('journeys')->select()->whereField('id', $id)->first();

        return $row ? $this->journeyFromRow($row) : null;
    }

    public function findBySlug(string $slug): ?Journey
    {
        $row = query('journeys')->select()->whereField('slug', $slug)->first();

        return $row ? $this->journeyFromRow($row) : null;
    }

    public function all(): array
    {
        $rows = query('journeys')->select()->orderBy('sort_order')->all();

        return array_map($this->journeyFromRow(...), $rows);
    }

    public function save(Journey $journey): Journey
    {
        $data = $this->journeyToRow($journey);

        if ($journey->id() === null) {
            $id = query('journeys')->insert($data)->execute();

            return $this->findById((int) $id->value);
        }

        query('journeys')->update(...$data)->whereField('id', $journey->id())->execute();

        return $this->findById($journey->id());
    }

    public function delete(int $id): void
    {
        query('journeys')->delete()->whereField('id', $id)->execute();
    }

    public function stepsForJourney(int $journeyId): array
    {
        $rows = query('journey_steps')
            ->select()
            ->whereField('journey_id', $journeyId)
            ->orderBy('sort_order')
            ->all();

        return array_map($this->stepFromRow(...), $rows);
    }

    public function findStepById(int $stepId): ?JourneyStep
    {
        $row = query('journey_steps')->select()->whereField('id', $stepId)->first();

        return $row ? $this->stepFromRow($row) : null;
    }

    public function saveStep(JourneyStep $step): JourneyStep
    {
        $data = $this->stepToRow($step);

        if ($step->id() === null) {
            $id = query('journey_steps')->insert($data)->execute();

            return $this->stepById((int) $id->value);
        }

        query('journey_steps')->update(...$data)->whereField('id', $step->id())->execute();

        return $this->stepById($step->id());
    }

    public function deleteStep(int $stepId): void
    {
        query('journey_steps')->delete()->whereField('id', $stepId)->execute();
    }

    public function componentsForStep(int $stepId): array
    {
        $rows = query('journey_step_components')
            ->select()
            ->whereField('journey_step_id', $stepId)
            ->orderBy('id')
            ->all();

        return array_map($this->stepComponentFromRow(...), $rows);
    }

    public function findStepComponentById(int $stepComponentId): ?JourneyStepComponent
    {
        $row = query('journey_step_components')->select()->whereField('id', $stepComponentId)->first();

        return $row ? $this->stepComponentFromRow($row) : null;
    }

    public function saveStepComponent(JourneyStepComponent $stepComponent): JourneyStepComponent
    {
        $data = $this->stepComponentToRow($stepComponent);

        if ($stepComponent->id() === null) {
            query('journey_step_components')->insert($data)->execute();

            return $this->stepComponentById($this->lastInsertId());
        }

        query('journey_step_components')
            ->update(...$data)
            ->whereField('id', $stepComponent->id())
            ->execute();

        return $this->stepComponentById($stepComponent->id());
    }

    public function deleteStepComponent(int $stepComponentId): void
    {
        query('journey_step_components')->delete()->whereField('id', $stepComponentId)->execute();
    }

    public function slugExists(string $slug, ?int $excludingId = null): bool
    {
        $builder = query('journeys')->select()->whereField('slug', $slug);

        if ($excludingId !== null) {
            $builder->andWhere('id', $excludingId, WhereOperator::NOT_EQUALS);
        }

        return $builder->first() !== null;
    }

    private function stepById(int $id): JourneyStep
    {
        $row = query('journey_steps')->select()->whereField('id', $id)->first();
        assert($row !== null);

        return $this->stepFromRow($row);
    }

    private function stepComponentById(int $id): JourneyStepComponent
    {
        $row = query('journey_step_components')->select()->whereField('id', $id)->first();
        assert($row !== null);

        return $this->stepComponentFromRow($row);
    }

    /** @param array<string, mixed> $row */
    private function journeyFromRow(array $row): Journey
    {
        return new Journey(
            id: (int) $row['id'],
            name: $row['name'],
            slug: $row['slug'],
            description: $row['description'],
            ownerId: $row['owner_id'] !== null ? (int) $row['owner_id'] : null,
            statusId: (int) $row['status_id'],
            sortOrder: (int) $row['sort_order'],
        );
    }

    /** @return array<string, mixed> */
    private function journeyToRow(Journey $journey): array
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $row = [
            'name' => $journey->name(),
            'slug' => $journey->slug(),
            'description' => $journey->description(),
            'owner_id' => $journey->ownerId(),
            'status_id' => $journey->statusId(),
            'sort_order' => $journey->sortOrder(),
            'updated_at' => $now,
        ];

        if ($journey->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }

    /** @param array<string, mixed> $row */
    private function stepFromRow(array $row): JourneyStep
    {
        return new JourneyStep(
            id: (int) $row['id'],
            journeyId: (int) $row['journey_id'],
            name: $row['name'],
            description: $row['description'],
            sortOrder: (int) $row['sort_order'],
        );
    }

    /** @return array<string, mixed> */
    private function stepToRow(JourneyStep $step): array
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $row = [
            'journey_id' => $step->journeyId(),
            'name' => $step->name(),
            'description' => $step->description(),
            'sort_order' => $step->sortOrder(),
            'updated_at' => $now,
        ];

        if ($step->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }

    /** @param array<string, mixed> $row */
    private function stepComponentFromRow(array $row): JourneyStepComponent
    {
        return new JourneyStepComponent(
            id: (int) $row['id'],
            journeyStepId: (int) $row['journey_step_id'],
            componentId: (int) $row['component_id'],
            roleInStep: $row['role_in_step'],
            notes: $row['notes'],
        );
    }

    /** @return array<string, mixed> */
    private function stepComponentToRow(JourneyStepComponent $stepComponent): array
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $row = [
            'journey_step_id' => $stepComponent->journeyStepId(),
            'component_id' => $stepComponent->componentId(),
            'role_in_step' => $stepComponent->roleInStep(),
            'notes' => $stepComponent->notes(),
            'updated_at' => $now,
        ];

        if ($stepComponent->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }
}
