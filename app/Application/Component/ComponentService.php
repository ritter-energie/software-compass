<?php

declare(strict_types=1);

namespace App\Application\Component;

use App\Application\Audit\AuditLogger;
use App\Application\Diagram\DiagramService;
use App\Domain\Component\Component;
use App\Domain\Component\ComponentRepository;
use App\Domain\Component\ComponentSearchCriteria;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Governance\GovernanceReview;
use App\Domain\Governance\GovernanceReviewRepository;
use App\Shared\ValueObject\Slug;
use RuntimeException;

/**
 * Application service for component use cases.
 *
 * Controllers delegate to this service so HTTP-specific code never contains
 * domain or persistence logic.
 */
final readonly class ComponentService
{
    public function __construct(
        private ComponentRepository $components,
        private DependencyRepository $dependencies,
        private GovernanceReviewRepository $governanceReviews,
        private DiagramService $diagramService,
        private AuditLogger $audit,
    ) {}

    public function create(CreateComponentCommand $command): Component
    {
        $component = new Component(
            id: null,
            name: $command->name,
            shortName: $command->shortName,
            slug: $this->uniqueSlug($command->name),
            componentTypeId: $command->componentTypeId,
            statusId: $command->statusId,
            criticalityId: $command->criticalityId,
            businessOwnerId: $command->businessOwnerId,
            technicalOwnerId: $command->technicalOwnerId,
            deploymentLocationId: $command->deploymentLocationId,
            environmentId: $command->environmentId,
            projectName: $command->projectName,
            startedOn: $command->startedOn,
            purpose: $command->purpose,
            description: $command->description,
            documentationUrl: $command->documentationUrl,
            repositoryUrl: $command->repositoryUrl,
            vendor: $command->vendor,
            lifecycleNotes: $command->lifecycleNotes,
            isExternal: $command->isExternal,
            parentComponentIds: $command->parentComponentIds,
            childComponentIds: $command->childComponentIds,
        );

        $component = $this->components->save($component);

        // MVP rule: every new component can be governed immediately. Later we
        // can restrict this to specific status IDs by resolving the status name.
        if ($this->governanceReviews->findByComponentId((int) $component->id()) === null) {
            $this->governanceReviews->save(new GovernanceReview(
                id: null,
                componentId: (int) $component->id(),
                reviewerId: null,
                reviewStatus: GovernanceReview::STATUS_OPEN,
                duplicateCheckDone: false,
                interfaceCheckDone: false,
                ownerCheckDone: false,
                dataCheckDone: false,
                deploymentCheckDone: false,
                notes: null,
                reviewedAt: null,
            ));
        }

        $this->audit->log('component', (int) $component->id(), 'created', null, $this->snapshot($component));

        return $component;
    }

    public function update(UpdateComponentCommand $command): Component
    {
        $existing = $this->components->findById($command->id) ?? throw new RuntimeException('Component not found.');

        $component = new Component(
            id: $existing->id(),
            name: $command->name,
            shortName: $command->shortName,
            slug: $existing->slug(),
            componentTypeId: $command->componentTypeId,
            statusId: $command->statusId,
            criticalityId: $command->criticalityId,
            businessOwnerId: $command->businessOwnerId,
            technicalOwnerId: $command->technicalOwnerId,
            deploymentLocationId: $command->deploymentLocationId,
            environmentId: $command->environmentId,
            projectName: $command->projectName,
            startedOn: $command->startedOn,
            purpose: $command->purpose,
            description: $command->description,
            documentationUrl: $command->documentationUrl,
            repositoryUrl: $command->repositoryUrl,
            vendor: $command->vendor,
            lifecycleNotes: $command->lifecycleNotes,
            isExternal: $command->isExternal,
            parentComponentIds: $command->parentComponentIds,
            childComponentIds: $command->childComponentIds,
        );

        $updated = $this->components->save($component);
        $this->audit->log('component', (int) $updated->id(), 'updated', $this->snapshot($existing), $this->snapshot($updated));

        return $updated;
    }

    public function delete(int $id): void
    {
        $existing = $this->components->findById($id);
        $this->components->delete($id);

        if ($existing !== null) {
            $this->audit->log('component', $id, 'deleted', $this->snapshot($existing), null);
        }
    }

    public function detail(int $id): ComponentDetailViewModel
    {
        $component = $this->components->findById($id) ?? throw new RuntimeException('Component not found.');

        $incoming = $this->dependencies->findIncomingForComponent($id);
        $outgoing = $this->dependencies->findOutgoingForComponent($id);
        $warnings = $component->incompletenessReasons();

        foreach ([...$incoming, ...$outgoing] as $dependency) {
            if ($dependency->isIncomplete()) {
                $warnings[] = sprintf('Dependency "%s" is incomplete', $dependency->name());
            }
        }

        return new ComponentDetailViewModel(
            component: $component,
            incomingDependencies: $incoming,
            outgoingDependencies: $outgoing,
            parentComponents: $this->components->parentsOf($id),
            childComponents: $this->components->childrenOf($id),
            governanceReview: $this->governanceReviews->findByComponentId($id),
            warnings: $warnings,
            mermaidDiagram: $this->diagramService->componentNeighborhood($id),
        );
    }

    /** @return Component[] */
    public function search(ComponentSearchCriteria $criteria): array
    {
        return $this->components->search($criteria);
    }

    /**
     * @return Component[] Potential duplicate solutions based on a simple
     * LIKE/token search. Good enough for the MVP and easy to replace with
     * MariaDB fulltext search later.
     */
    public function findSimilar(string $purpose, ?string $name = null): array
    {
        $tokens = array_filter(preg_split('/\W+/', strtolower($purpose . ' ' . ($name ?? ''))) ?: []);
        $matches = [];

        foreach (array_slice(array_unique($tokens), 0, 8) as $token) {
            if (mb_strlen($token) < 3) {
                continue;
            }

            foreach ($this->components->search(new ComponentSearchCriteria(query: $token)) as $component) {
                $matches[$component->id() ?? spl_object_id($component)] = $component;
            }
        }

        return array_values($matches);
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Slug::fromText($name);
        $candidate = (string) $slug;
        $suffix = 2;

        while ($this->components->slugExists($candidate)) {
            $candidate = (string) $slug->withSuffix($suffix++);
        }

        return $candidate;
    }

    /** @return array<string, mixed> */
    private function snapshot(Component $component): array
    {
        return [
            'id' => $component->id(),
            'name' => $component->name(),
            'slug' => $component->slug(),
            'component_type_id' => $component->componentTypeId(),
            'status_id' => $component->statusId(),
            'criticality_id' => $component->criticalityId(),
            'business_owner_id' => $component->businessOwnerId(),
            'technical_owner_id' => $component->technicalOwnerId(),
            'deployment_location_id' => $component->deploymentLocationId(),
            'environment_id' => $component->environmentId(),
            'purpose' => $component->purpose(),
            'documentation_url' => $component->documentationUrl(),
            'parent_component_ids' => $component->parentComponentIds(),
            'child_component_ids' => $component->childComponentIds(),
        ];
    }
}
