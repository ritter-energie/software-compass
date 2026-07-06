<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\Diagram\ComponentDiagramFilter;
use App\Application\Diagram\DiagramService;
use App\Domain\Component\Component;
use App\Domain\Component\ComponentRepository;
use App\Domain\Component\ComponentSearchCriteria;
use App\Domain\Dependency\Dependency;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Dependency\DependencySearchCriteria;
use App\Domain\Journey\Journey;
use App\Domain\Journey\JourneyRepository;
use App\Domain\Journey\JourneyStep;
use App\Domain\Journey\JourneyStepComponent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DiagramServiceTest extends TestCase
{
    public function test_component_overview_renders_nodes_and_edges(): void
    {
        $service = new DiagramService(
            components: $this->componentRepository([
                $this->component(id: 1, name: 'CRM "Core"'),
                $this->component(id: 2, name: 'ERP'),
            ]),
            dependencies: $this->dependencyRepository([
                $this->dependency(id: 1, source: 1, target: 2, name: 'Orders', dataDescription: 'Order Data'),
            ]),
            journeys: $this->journeyRepository([], [], []),
        );

        $diagram = $service->componentOverview(new ComponentDiagramFilter());

        $this->assertStringContainsString('flowchart LR', $diagram);
        $this->assertStringContainsString('C1["CRM #quot;Core#quot;"]', $diagram);
        $this->assertStringContainsString('C2["ERP"]', $diagram);
        $this->assertStringContainsString('C1 -->|"Orders / Order Data"| C2', $diagram);
    }

    public function test_component_overview_renders_parent_components_as_containers(): void
    {
        $service = new DiagramService(
            components: $this->componentRepository([
                $this->component(id: 1, name: 'Platform'),
                $this->component(id: 2, name: 'CRM', parentComponentIds: [1]),
                $this->component(id: 3, name: 'Shared Services', parentComponentIds: [1, 4]),
                $this->component(id: 4, name: 'Operations'),
            ]),
            dependencies: $this->dependencyRepository([]),
            journeys: $this->journeyRepository([], [], []),
        );

        $diagram = $service->componentOverview(new ComponentDiagramFilter());

        $this->assertStringContainsString('subgraph SGC1["Platform"]', $diagram);
        $this->assertStringContainsString('        C2["CRM"]', $diagram);
        $this->assertStringContainsString('        C3["Shared Services"]', $diagram);
        $this->assertStringContainsString('subgraph SGC4["Operations"]', $diagram);
        $this->assertStringContainsString('C3 -. "inherits" .-> C4', $diagram);
    }

    public function test_component_neighborhood_throws_for_unknown_component(): void
    {
        $service = new DiagramService(
            components: $this->componentRepository([]),
            dependencies: $this->dependencyRepository([]),
            journeys: $this->journeyRepository([], [], []),
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Component not found.');

        $service->componentNeighborhood(componentId: 999);
    }

    public function test_journey_diagram_renders_steps_and_component_roles(): void
    {
        $service = new DiagramService(
            components: $this->componentRepository([
                $this->component(id: 1, name: 'Webshop'),
                $this->component(id: 2, name: 'ERP'),
            ]),
            dependencies: $this->dependencyRepository([]),
            journeys: $this->journeyRepository(
                journeys: [
                    new Journey(
                        id: 10,
                        name: 'Order to Delivery',
                        slug: 'order-to-delivery',
                        description: null,
                        ownerId: null,
                        ownerTeamId: null,
                        statusId: 1,
                        sortOrder: 1,
                    ),
                ],
                stepsByJourney: [
                    10 => [
                        new JourneyStep(id: 100, journeyId: 10, name: 'Customer places order', description: null, sortOrder: 1),
                        new JourneyStep(id: 101, journeyId: 10, name: 'ERP creates order', description: null, sortOrder: 2),
                    ],
                ],
                componentsByStep: [
                    100 => [new JourneyStepComponent(id: 1000, journeyStepId: 100, componentId: 1, roleInStep: 'leading', notes: null)],
                    101 => [new JourneyStepComponent(id: 1001, journeyStepId: 101, componentId: 2, roleInStep: 'target_system', notes: null)],
                ],
            ),
        );

        $diagram = $service->journeyDiagram(journeyId: 10);

        $this->assertStringContainsString('%% Journey: Order to Delivery', $diagram);
        $this->assertStringContainsString('S100["1. Customer places order"]', $diagram);
        $this->assertStringContainsString('S101["2. ERP creates order"]', $diagram);
        $this->assertStringContainsString('S100 --> S101', $diagram);
        $this->assertStringContainsString('S100 -. "leading" .-> C1', $diagram);
        $this->assertStringContainsString('S101 -. "target_system" .-> C2', $diagram);
    }

    /** @param int[] $parentComponentIds */
    private function component(int $id, string $name, array $parentComponentIds = []): Component
    {
        return new Component(
            id: $id,
            name: $name,
            shortName: null,
            slug: strtolower(str_replace(' ', '-', $name)),
            componentTypeId: 1,
            statusId: 1,
            criticalityId: null,
            businessOwnerId: 1,
            businessOwnerTeamId: null,
            technicalOwnerId: 1,
            technicalOwnerTeamId: null,
            deploymentLocationId: 1,
            environmentId: 1,
            projectName: null,
            startedOn: null,
            purpose: 'Test purpose',
            description: null,
            documentationUrl: null,
            repositoryUrl: null,
            vendor: null,
            lifecycleNotes: null,
            isExternal: false,
            parentComponentIds: $parentComponentIds,
        );
    }

    private function dependency(int $id, int $source, int $target, string $name, ?string $dataDescription): Dependency
    {
        return new Dependency(
            id: $id,
            sourceComponentId: $source,
            targetComponentId: $target,
            dependencyTypeId: 1,
            protocolId: null,
            statusId: 1,
            criticalityId: null,
            ownerId: 1,
            ownerTeamId: null,
            name: $name,
            description: null,
            dataDescription: $dataDescription,
            frequency: null,
            direction: Dependency::DIRECTION_SOURCE_TO_TARGET,
            authenticationMethod: null,
            documentationUrl: null,
            technicalNotes: null,
            isBidirectional: false,
        );
    }

    /** @param Component[] $components */
    private function componentRepository(array $components): ComponentRepository
    {
        return new class($components) implements ComponentRepository {
            /** @var array<int, Component> */
            private array $items;

            /** @param Component[] $components */
            public function __construct(array $components)
            {
                $this->items = [];
                foreach ($components as $component) {
                    $this->items[(int) $component->id()] = $component;
                }
            }

            public function findById(int $id): ?Component
            {
                return $this->items[$id] ?? null;
            }

            public function findBySlug(string $slug): ?Component
            {
                foreach ($this->items as $component) {
                    if ($component->slug() === $slug) {
                        return $component;
                    }
                }

                return null;
            }

            public function search(ComponentSearchCriteria $criteria): array
            {
                return array_values($this->items);
            }

            public function all(): array
            {
                return array_values($this->items);
            }

            public function parentsOf(int $componentId): array
            {
                $component = $this->items[$componentId] ?? null;

                if ($component === null) {
                    return [];
                }

                return array_values(array_filter(
                    $this->items,
                    static fn (Component $candidate): bool => in_array($candidate->id(), $component->parentComponentIds(), true),
                ));
            }

            public function childrenOf(int $componentId): array
            {
                return array_values(array_filter(
                    $this->items,
                    static fn (Component $candidate): bool => in_array($componentId, $candidate->parentComponentIds(), true),
                ));
            }

            public function save(Component $component): Component
            {
                $this->items[(int) $component->id()] = $component;

                return $component;
            }

            public function delete(int $id): void
            {
                unset($this->items[$id]);
            }

            public function slugExists(string $slug, ?int $excludingId = null): bool
            {
                foreach ($this->items as $id => $component) {
                    if ($excludingId !== null && $excludingId === $id) {
                        continue;
                    }
                    if ($component->slug() === $slug) {
                        return true;
                    }
                }

                return false;
            }
        };
    }

    /** @param Dependency[] $dependencies */
    private function dependencyRepository(array $dependencies): DependencyRepository
    {
        return new class($dependencies) implements DependencyRepository {
            /** @var array<int, Dependency> */
            private array $items;

            /** @param Dependency[] $dependencies */
            public function __construct(array $dependencies)
            {
                $this->items = [];
                foreach ($dependencies as $dependency) {
                    $this->items[(int) $dependency->id()] = $dependency;
                }
            }

            public function findById(int $id): ?Dependency
            {
                return $this->items[$id] ?? null;
            }

            public function findByComponentId(int $componentId): array
            {
                return array_values(array_filter(
                    $this->items,
                    static fn (Dependency $dependency): bool => $dependency->sourceComponentId() === $componentId || $dependency->targetComponentId() === $componentId,
                ));
            }

            public function findIncomingForComponent(int $componentId): array
            {
                return array_values(array_filter(
                    $this->items,
                    static fn (Dependency $dependency): bool => $dependency->targetComponentId() === $componentId,
                ));
            }

            public function findOutgoingForComponent(int $componentId): array
            {
                return array_values(array_filter(
                    $this->items,
                    static fn (Dependency $dependency): bool => $dependency->sourceComponentId() === $componentId,
                ));
            }

            public function search(DependencySearchCriteria $criteria): array
            {
                return array_values($this->items);
            }

            public function all(): array
            {
                return array_values($this->items);
            }

            public function save(Dependency $dependency): Dependency
            {
                $this->items[(int) $dependency->id()] = $dependency;

                return $dependency;
            }

            public function delete(int $id): void
            {
                unset($this->items[$id]);
            }
        };
    }

    /**
     * @param Journey[] $journeys
     * @param array<int, JourneyStep[]> $stepsByJourney
     * @param array<int, JourneyStepComponent[]> $componentsByStep
     */
    private function journeyRepository(array $journeys, array $stepsByJourney, array $componentsByStep): JourneyRepository
    {
        return new class($journeys, $stepsByJourney, $componentsByStep) implements JourneyRepository {
            /** @var array<int, Journey> */
            private array $journeysById = [];

            /** @param Journey[] $journeys @param array<int, JourneyStep[]> $stepsByJourney @param array<int, JourneyStepComponent[]> $componentsByStep */
            public function __construct(
                array $journeys,
                private array $stepsByJourney,
                private array $componentsByStep,
            ) {
                foreach ($journeys as $journey) {
                    $this->journeysById[(int) $journey->id()] = $journey;
                }
            }

            public function findById(int $id): ?Journey
            {
                return $this->journeysById[$id] ?? null;
            }

            public function findBySlug(string $slug): ?Journey
            {
                foreach ($this->journeysById as $journey) {
                    if ($journey->slug() === $slug) {
                        return $journey;
                    }
                }

                return null;
            }

            public function all(): array
            {
                return array_values($this->journeysById);
            }

            public function save(Journey $journey): Journey
            {
                $this->journeysById[(int) $journey->id()] = $journey;

                return $journey;
            }

            public function delete(int $id): void
            {
                unset($this->journeysById[$id]);
            }

            public function stepsForJourney(int $journeyId): array
            {
                return $this->stepsByJourney[$journeyId] ?? [];
            }

            public function findStepById(int $stepId): ?JourneyStep
            {
                foreach ($this->stepsByJourney as $steps) {
                    foreach ($steps as $step) {
                        if ($step->id() === $stepId) {
                            return $step;
                        }
                    }
                }

                return null;
            }

            public function saveStep(JourneyStep $step): JourneyStep
            {
                return $step;
            }

            public function deleteStep(int $stepId): void
            {
                foreach ($this->stepsByJourney as $journeyId => $steps) {
                    $this->stepsByJourney[$journeyId] = array_values(array_filter(
                        $steps,
                        static fn (JourneyStep $step): bool => $step->id() !== $stepId,
                    ));
                }
            }

            public function componentsForStep(int $stepId): array
            {
                return $this->componentsByStep[$stepId] ?? [];
            }

            public function findStepComponentById(int $stepComponentId): ?JourneyStepComponent
            {
                foreach ($this->componentsByStep as $components) {
                    foreach ($components as $component) {
                        if ($component->id() === $stepComponentId) {
                            return $component;
                        }
                    }
                }

                return null;
            }

            public function saveStepComponent(JourneyStepComponent $stepComponent): JourneyStepComponent
            {
                return $stepComponent;
            }

            public function deleteStepComponent(int $stepComponentId): void
            {
                foreach ($this->componentsByStep as $stepId => $components) {
                    $this->componentsByStep[$stepId] = array_values(array_filter(
                        $components,
                        static fn (JourneyStepComponent $component): bool => $component->id() !== $stepComponentId,
                    ));
                }
            }

            public function slugExists(string $slug, ?int $excludingId = null): bool
            {
                foreach ($this->journeysById as $id => $journey) {
                    if ($excludingId !== null && $excludingId === $id) {
                        continue;
                    }
                    if ($journey->slug() === $slug) {
                        return true;
                    }
                }

                return false;
            }
        };
    }
}
