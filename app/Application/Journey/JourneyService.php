<?php
declare(strict_types=1);

namespace App\Application\Journey;

use App\Application\Audit\AuditLogger;
use App\Domain\Journey\Journey;
use App\Domain\Journey\JourneyRepository;
use App\Domain\Journey\JourneyStep;
use App\Domain\Journey\JourneyStepComponent;
use App\Shared\ValueObject\Slug;
use RuntimeException;

final readonly class JourneyService
{
    public function __construct(
        private JourneyRepository $journeys,
        private AuditLogger $audit,
    ) {}

    public function create(CreateJourneyCommand $command): Journey
    {
        $journey = $this->journeys->save(new Journey(
            id: null,
            name: $command->name,
            slug: $this->uniqueSlug($command->name),
            description: $command->description,
            ownerId: $command->ownerId,
            ownerTeamId: $command->ownerTeamId,
            statusId: $command->statusId,
            sortOrder: $command->sortOrder,
        ));
        $this->audit->log('journey', (int) $journey->id(), 'created', null, $this->snapshot($journey));

        return $journey;
    }

    public function update(UpdateJourneyCommand $command): Journey
    {
        $existing = $this->journeys->findById($command->id) ?? throw new RuntimeException('Journey not found.');

        $updated = $this->journeys->save(new Journey(
            id: $existing->id(),
            name: $command->name,
            slug: $existing->slug(),
            description: $command->description,
            ownerId: $command->ownerId,
            ownerTeamId: $command->ownerTeamId,
            statusId: $command->statusId,
            sortOrder: $command->sortOrder,
        ));
        $this->audit->log('journey', (int) $updated->id(), 'updated', $this->snapshot($existing), $this->snapshot($updated));

        return $updated;
    }

    public function delete(int $id): void
    {
        $existing = $this->journeys->findById($id);
        $this->journeys->delete($id);
        if ($existing !== null) {
            $this->audit->log('journey', $id, 'deleted', $this->snapshot($existing), null);
        }
    }

    /** @return Journey[] */
    public function all(): array
    {
        return $this->journeys->all();
    }

    public function detail(int $id): Journey
    {
        return $this->journeys->findById($id) ?? throw new RuntimeException('Journey not found.');
    }

    /** @return JourneyStep[] */
    public function stepsForJourney(int $journeyId): array
    {
        return $this->journeys->stepsForJourney($journeyId);
    }

    /** @return JourneyStepComponent[] */
    public function componentsForStep(int $stepId): array
    {
        return $this->journeys->componentsForStep($stepId);
    }

    public function step(int $stepId): JourneyStep
    {
        return $this->journeys->findStepById($stepId) ?? throw new RuntimeException('Journey step not found.');
    }

    public function addStep(AddJourneyStepCommand $command): JourneyStep
    {
        return $this->journeys->saveStep(new JourneyStep(
            id: null,
            journeyId: $command->journeyId,
            name: $command->name,
            description: $command->description,
            sortOrder: $command->sortOrder,
        ));
    }

    public function updateStep(UpdateJourneyStepCommand $command): JourneyStep
    {
        if ($this->journeys->findStepById($command->id) === null) {
            throw new RuntimeException('Journey step not found.');
        }

        return $this->journeys->saveStep(new JourneyStep(
            id: $command->id,
            journeyId: $command->journeyId,
            name: $command->name,
            description: $command->description,
            sortOrder: $command->sortOrder,
        ));
    }

    public function deleteStep(int $stepId): void
    {
        $this->journeys->deleteStep($stepId);
    }

    public function attachComponent(int $stepId, int $componentId, string $roleInStep, ?string $notes): JourneyStepComponent
    {
        if (! in_array($roleInStep, JourneyStepComponent::validRoles(), true)) {
            throw new RuntimeException('Invalid role in step.');
        }

        return $this->journeys->saveStepComponent(new JourneyStepComponent(
            id: null,
            journeyStepId: $stepId,
            componentId: $componentId,
            roleInStep: $roleInStep,
            notes: $notes,
        ));
    }

    public function deleteStepComponent(int $stepComponentId): void
    {
        $this->journeys->deleteStepComponent($stepComponentId);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Slug::fromText($name);
        $candidate = (string) $slug;
        $suffix = 2;
        while ($this->journeys->slugExists($candidate)) {
            $candidate = (string) $slug->withSuffix($suffix++);
        }
        return $candidate;
    }

    /** @return array<string, mixed> */
    private function snapshot(Journey $journey): array
    {
        return [
            'id' => $journey->id(),
            'name' => $journey->name(),
            'slug' => $journey->slug(),
            'description' => $journey->description(),
            'owner_id' => $journey->ownerId(),
            'owner_team_id' => $journey->ownerTeamId(),
            'status_id' => $journey->statusId(),
            'sort_order' => $journey->sortOrder(),
        ];
    }
}
