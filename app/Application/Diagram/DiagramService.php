<?php

declare(strict_types=1);

namespace App\Application\Diagram;

use App\Domain\Component\Component;
use App\Domain\Component\ComponentRepository;
use App\Domain\Component\ComponentSearchCriteria;
use App\Domain\Dependency\Dependency;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Journey\JourneyRepository;
use App\Shared\Support\MermaidSanitizer;
use RuntimeException;

/**
 * Generates Mermaid.js diagrams from current database state.
 *
 * Mermaid source is never persisted. This ensures diagrams immediately reflect
 * the latest components, dependencies and journey assignments.
 */
final readonly class DiagramService
{
    public function __construct(
        private ComponentRepository $components,
        private DependencyRepository $dependencies,
        private JourneyRepository $journeys,
    ) {}

    public function componentOverview(ComponentDiagramFilter $filter): string
    {
        $components = $this->components->search(new ComponentSearchCriteria(
            componentTypeId: $filter->componentTypeId,
            statusId: $filter->statusId,
            criticalityId: $filter->criticalityId,
            ownerId: $filter->ownerId,
            ownerTeamId: $filter->ownerTeamId,
        ));

        if ($filter->componentId !== null) {
            $center = $this->components->findById($filter->componentId);
            $components = $center ? [$center] : [];
        }

        $components = array_slice($components, 0, $filter->maxNodes);
        $componentIds = array_flip(array_map(static fn (Component $component): int => (int) $component->id(), $components));

        $dependencies = array_filter(
            $this->dependencies->all(),
            static fn (Dependency $dependency): bool => isset($componentIds[$dependency->sourceComponentId()], $componentIds[$dependency->targetComponentId()]),
        );

        return $this->renderComponentGraph($components, $dependencies);
    }

    public function componentNeighborhood(int $componentId, int $depth = 1): string
    {
        $center = $this->components->findById($componentId) ?? throw new RuntimeException('Component not found.');

        $dependencies = $this->dependencies->findByComponentId($componentId);
        $components = [$center->id() => $center];

        foreach ($dependencies as $dependency) {
            foreach ([$dependency->sourceComponentId(), $dependency->targetComponentId()] as $id) {
                if (! isset($components[$id]) && ($component = $this->components->findById($id)) !== null) {
                    $components[$id] = $component;
                }
            }
        }

        foreach ([...$this->components->parentsOf($componentId), ...$this->components->childrenOf($componentId)] as $relatedComponent) {
            if ($relatedComponent->id() !== null) {
                $components[$relatedComponent->id()] = $relatedComponent;
            }
        }

        if ($depth > 1) {
            foreach (array_keys($components) as $neighborId) {
                foreach ($this->dependencies->findByComponentId((int) $neighborId) as $dependency) {
                    $dependencies[] = $dependency;
                    foreach ([$dependency->sourceComponentId(), $dependency->targetComponentId()] as $id) {
                        if (! isset($components[$id]) && ($component = $this->components->findById($id)) !== null) {
                            $components[$id] = $component;
                        }
                    }
                }

                foreach ([...$this->components->parentsOf((int) $neighborId), ...$this->components->childrenOf((int) $neighborId)] as $relatedComponent) {
                    if ($relatedComponent->id() !== null) {
                        $components[$relatedComponent->id()] = $relatedComponent;
                    }
                }
            }
        }

        return $this->renderComponentGraph(array_values($components), $dependencies);
    }

    public function journeyDiagram(int $journeyId): string
    {
        $journey = $this->journeys->findById($journeyId) ?? throw new RuntimeException('Journey not found.');

        $lines = [
            'flowchart LR',
            '    %% Journey: ' . MermaidSanitizer::label($journey->name()),
        ];

        $steps = $this->journeys->stepsForJourney($journeyId);
        $previousStepId = null;
        $definedComponents = [];

        foreach ($steps as $index => $step) {
            $stepId = MermaidSanitizer::nodeId('S', (int) $step->id());
            $label = MermaidSanitizer::label(sprintf('%d. %s', $index + 1, $step->name()));
            $lines[] = sprintf('    %s["%s"]', $stepId, $label);

            if ($previousStepId !== null) {
                $lines[] = sprintf('    %s --> %s', $previousStepId, $stepId);
            }

            foreach ($this->journeys->componentsForStep((int) $step->id()) as $assignment) {
                $component = $this->components->findById($assignment->componentId());
                if ($component === null) {
                    continue;
                }

                $componentId = MermaidSanitizer::nodeId('C', (int) $component->id());
                if (! isset($definedComponents[$componentId])) {
                    $lines[] = sprintf('    %s["%s"]', $componentId, MermaidSanitizer::label($component->name()));
                    $definedComponents[$componentId] = true;
                }

                $lines[] = sprintf('    %s -. "%s" .-> %s', $stepId, MermaidSanitizer::label($assignment->roleInStep()), $componentId);
            }

            $previousStepId = $stepId;
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param Component[] $components
     * @param Dependency[] $dependencies
     */
    private function renderComponentGraph(array $components, array $dependencies): string
    {
        $lines = ['flowchart LR'];
        $defined = [];
        $componentsById = [];
        $childrenByParent = [];
        $primaryParentByChild = [];

        foreach ($components as $component) {
            if ($component->id() === null) {
                continue;
            }

            $componentsById[(int) $component->id()] = $component;
        }

        foreach ($componentsById as $componentId => $component) {
            foreach ($component->parentComponentIds() as $parentComponentId) {
                if (! isset($componentsById[$parentComponentId])) {
                    continue;
                }

                $childrenByParent[$parentComponentId][] = $componentId;
                $primaryParentByChild[$componentId] ??= $parentComponentId;
            }
        }

        foreach ($childrenByParent as $parentComponentId => $childComponentIds) {
            $parentComponent = $componentsById[$parentComponentId];
            $lines[] = sprintf('    subgraph %s["%s"]', MermaidSanitizer::nodeId('SGC', $parentComponentId), MermaidSanitizer::label($parentComponent->name()));

            if (! isset($primaryParentByChild[$parentComponentId])) {
                $parentNodeId = MermaidSanitizer::nodeId('C', $parentComponentId);
                $defined[$parentNodeId] = true;
                $lines[] = sprintf('        %s["%s"]', $parentNodeId, MermaidSanitizer::label($parentComponent->name()));
            }

            foreach ($childComponentIds as $childComponentId) {
                if (($primaryParentByChild[$childComponentId] ?? null) !== $parentComponentId) {
                    continue;
                }

                $childComponent = $componentsById[$childComponentId];
                $childNodeId = MermaidSanitizer::nodeId('C', $childComponentId);
                $defined[$childNodeId] = true;
                $lines[] = sprintf('        %s["%s"]', $childNodeId, MermaidSanitizer::label($childComponent->name()));
            }

            $lines[] = '    end';
        }

        foreach ($componentsById as $componentId => $component) {
            $nodeId = MermaidSanitizer::nodeId('C', (int) $component->id());

            if (isset($defined[$nodeId])) {
                continue;
            }

            $defined[$nodeId] = true;
            $lines[] = sprintf('    %s["%s"]', $nodeId, MermaidSanitizer::label($component->name()));
        }

        foreach ($dependencies as $dependency) {
            $source = MermaidSanitizer::nodeId('C', $dependency->sourceComponentId());
            $target = MermaidSanitizer::nodeId('C', $dependency->targetComponentId());

            if (! isset($defined[$source], $defined[$target])) {
                continue;
            }

            $label = MermaidSanitizer::label($dependency->label() . ($dependency->isBidirectional() ? ' / bidirectional' : ''));
            $lines[] = sprintf('    %s -->|"%s"| %s', $source, $label, $target);

            if ($dependency->isBidirectional()) {
                $lines[] = sprintf('    %s -->|"%s"| %s', $target, $label, $source);
            }
        }

        $inheritanceEdges = [];
        foreach ($components as $component) {
            if ($component->id() === null) {
                continue;
            }

            $child = MermaidSanitizer::nodeId('C', (int) $component->id());
            foreach ($component->parentComponentIds() as $parentComponentId) {
                $parent = MermaidSanitizer::nodeId('C', $parentComponentId);

                if (! isset($defined[$child], $defined[$parent])) {
                    continue;
                }

                $edgeKey = $child . '->' . $parent;
                if (isset($inheritanceEdges[$edgeKey])) {
                    continue;
                }

                $inheritanceEdges[$edgeKey] = true;
                $lines[] = sprintf('    %s -. "%s" .-> %s', $child, MermaidSanitizer::label('inherits'), $parent);
            }
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
