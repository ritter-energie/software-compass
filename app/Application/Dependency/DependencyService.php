<?php

declare(strict_types=1);

namespace App\Application\Dependency;

use App\Application\Audit\AuditLogger;
use App\Domain\Dependency\Dependency;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Dependency\DependencySearchCriteria;
use RuntimeException;

/** Application service for dependency/interface use cases. */
final readonly class DependencyService
{
    public function __construct(
        private DependencyRepository $dependencies,
        private AuditLogger $audit,
    ) {}

    public function create(CreateDependencyCommand $command): Dependency
    {
        $dependency = $this->dependencies->save($this->fromCreate($command));
        $this->audit->log('dependency', (int) $dependency->id(), 'created', null, $this->snapshot($dependency));

        return $dependency;
    }

    public function update(UpdateDependencyCommand $command): Dependency
    {
        $existing = $this->dependencies->findById($command->id);
        if ($existing === null) {
            throw new RuntimeException('Dependency not found.');
        }

        $updated = $this->dependencies->save($this->fromUpdate($command));
        $this->audit->log('dependency', (int) $updated->id(), 'updated', $this->snapshot($existing), $this->snapshot($updated));

        return $updated;
    }

    public function delete(int $id): void
    {
        $existing = $this->dependencies->findById($id);
        $this->dependencies->delete($id);

        if ($existing !== null) {
            $this->audit->log('dependency', $id, 'deleted', $this->snapshot($existing), null);
        }
    }

    public function forComponent(int $componentId): ComponentDependenciesViewModel
    {
        return new ComponentDependenciesViewModel(
            componentId: $componentId,
            incoming: $this->dependencies->findIncomingForComponent($componentId),
            outgoing: $this->dependencies->findOutgoingForComponent($componentId),
        );
    }

    /** @return Dependency[] */
    public function search(DependencySearchCriteria $criteria): array
    {
        return $this->dependencies->search($criteria);
    }

    private function fromCreate(CreateDependencyCommand $command): Dependency
    {
        return new Dependency(
            id: null,
            sourceComponentId: $command->sourceComponentId,
            targetComponentId: $command->targetComponentId,
            dependencyTypeId: $command->dependencyTypeId,
            protocolId: $command->protocolId,
            statusId: $command->statusId,
            criticalityId: $command->criticalityId,
            ownerId: $command->ownerId,
            ownerTeamId: $command->ownerTeamId,
            name: $command->name,
            description: $command->description,
            dataDescription: $command->dataDescription,
            frequency: $command->frequency,
            direction: $command->direction,
            authenticationMethod: $command->authenticationMethod,
            documentationUrl: $command->documentationUrl,
            technicalNotes: $command->technicalNotes,
            isBidirectional: $command->isBidirectional,
        );
    }

    private function fromUpdate(UpdateDependencyCommand $command): Dependency
    {
        return new Dependency(
            id: $command->id,
            sourceComponentId: $command->sourceComponentId,
            targetComponentId: $command->targetComponentId,
            dependencyTypeId: $command->dependencyTypeId,
            protocolId: $command->protocolId,
            statusId: $command->statusId,
            criticalityId: $command->criticalityId,
            ownerId: $command->ownerId,
            ownerTeamId: $command->ownerTeamId,
            name: $command->name,
            description: $command->description,
            dataDescription: $command->dataDescription,
            frequency: $command->frequency,
            direction: $command->direction,
            authenticationMethod: $command->authenticationMethod,
            documentationUrl: $command->documentationUrl,
            technicalNotes: $command->technicalNotes,
            isBidirectional: $command->isBidirectional,
        );
    }

    /** @return array<string, mixed> */
    private function snapshot(Dependency $dependency): array
    {
        return [
            'id' => $dependency->id(),
            'source_component_id' => $dependency->sourceComponentId(),
            'target_component_id' => $dependency->targetComponentId(),
            'dependency_type_id' => $dependency->dependencyTypeId(),
            'protocol_id' => $dependency->protocolId(),
            'status_id' => $dependency->statusId(),
            'criticality_id' => $dependency->criticalityId(),
            'owner_id' => $dependency->ownerId(),
            'owner_team_id' => $dependency->ownerTeamId(),
            'name' => $dependency->name(),
            'data_description' => $dependency->dataDescription(),
            'frequency' => $dependency->frequency(),
            'documentation_url' => $dependency->documentationUrl(),
        ];
    }
}
