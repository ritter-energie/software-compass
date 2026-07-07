<?php

declare(strict_types=1);

namespace App\Domain\Component;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * The central entity of Software Compass: a software component, tool,
 * application, API, or any other piece of the technology landscape.
 *
 * This class is intentionally framework-agnostic: it knows nothing about
 * HTTP, views or the database. Domain invariants (e.g. "name must not be
 * blank") are enforced here, not in controllers or repositories.
 */
final class Component {
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $shortName,
        private string $slug,
        private int $componentTypeId,
        private int $statusId,
        private ?int $criticalityId,
        private ?int $businessOwnerId,
        private ?int $businessOwnerTeamId,
        private ?int $technicalOwnerId,
        private ?int $technicalOwnerTeamId,
        private ?int $deploymentLocationId,
        private ?int $environmentId,
        private ?string $projectName,
        private ?DateTimeImmutable $startedOn,
        private ?string $purpose,
        private ?string $description,
        private ?string $documentationUrl,
        private ?string $repositoryUrl,
        private ?string $vendor,
        private ?string $lifecycleNotes,
        private bool $isExternal,
        private ?int $parentComponentId = null,
        private array $childComponentIds = [],
    ) {
        $this->guardNameIsNotBlank($name);
        $this->parentComponentId = $parentComponentId;
        $this->childComponentIds = $this->normalizeRelatedComponentIds($childComponentIds);
        $this->guardParentIdIsPositive();
        $this->guardInheritanceDoesNotReferenceSelf();
        $this->guardInheritanceDoesNotConflict();
    }

    public function id(): ?int {
        return $this->id;
    }

    public function name(): string {
        return $this->name;
    }

    public function shortName(): ?string {
        return $this->shortName;
    }

    public function slug(): string {
        return $this->slug;
    }

    public function componentTypeId(): int {
        return $this->componentTypeId;
    }

    public function statusId(): int {
        return $this->statusId;
    }

    public function criticalityId(): ?int {
        return $this->criticalityId;
    }

    public function businessOwnerId(): ?int {
        return $this->businessOwnerId;
    }

    public function businessOwnerTeamId(): ?int {
        return $this->businessOwnerTeamId;
    }

    public function technicalOwnerId(): ?int {
        return $this->technicalOwnerId;
    }

    public function technicalOwnerTeamId(): ?int {
        return $this->technicalOwnerTeamId;
    }

    public function deploymentLocationId(): ?int {
        return $this->deploymentLocationId;
    }

    public function environmentId(): ?int {
        return $this->environmentId;
    }

    public function projectName(): ?string {
        return $this->projectName;
    }

    public function startedOn(): ?DateTimeImmutable {
        return $this->startedOn;
    }

    public function purpose(): ?string {
        return $this->purpose;
    }

    public function description(): ?string {
        return $this->description;
    }

    public function documentationUrl(): ?string {
        return $this->documentationUrl;
    }

    public function repositoryUrl(): ?string {
        return $this->repositoryUrl;
    }

    public function vendor(): ?string {
        return $this->vendor;
    }

    public function lifecycleNotes(): ?string {
        return $this->lifecycleNotes;
    }

    public function isExternal(): bool {
        return $this->isExternal;
    }

    public function parentComponentId(): ?int {
        return $this->parentComponentId;
    }

    /**
     * @return int[]
     */
    public function childComponentIds(): array {
        return $this->childComponentIds;
    }

    public function rename(string $name): void {
        $this->guardNameIsNotBlank($name);

        $this->name = $name;
    }

    public function changePurpose(?string $purpose): void {
        $this->purpose = $purpose;
    }

    public function assignBusinessOwner(?int $personId): void {
        $this->businessOwnerId = $personId;
    }

    public function assignBusinessOwnerTeam(?int $teamId): void {
        $this->businessOwnerTeamId = $teamId;
    }

    public function assignTechnicalOwner(?int $personId): void {
        $this->technicalOwnerId = $personId;
    }

    public function assignTechnicalOwnerTeam(?int $teamId): void {
        $this->technicalOwnerTeamId = $teamId;
    }

    public function markExternal(bool $isExternal): void {
        $this->isExternal = $isExternal;
    }

    /**
     * A component is considered incomplete when it is missing information
     * that governance requires before it may go live.
     */
    public function isIncomplete(): bool {
        return (
            $this->businessOwnerId === null
            && $this->businessOwnerTeamId === null
            || $this->technicalOwnerId === null
            && $this->technicalOwnerTeamId === null
            || $this->purpose === null
            || trim((string) $this->purpose) === ''
            || $this->deploymentLocationId === null
            || $this->environmentId === null
        );
    }

    /**
     * @return string[] Human-readable reasons this component is incomplete.
     */
    public function incompletenessReasons(): array {
        $reasons = [];

        if ($this->businessOwnerId === null && $this->businessOwnerTeamId === null) {
            $reasons[] = 'No business owner assigned';
        }

        if ($this->technicalOwnerId === null && $this->technicalOwnerTeamId === null) {
            $reasons[] = 'No technical owner assigned';
        }

        if ($this->purpose === null || trim($this->purpose) === '') {
            $reasons[] = 'No purpose documented';
        }

        if ($this->deploymentLocationId === null) {
            $reasons[] = 'No deployment location set';
        }

        if ($this->environmentId === null) {
            $reasons[] = 'No environment set';
        }

        return $reasons;
    }

    private function guardNameIsNotBlank(string $name): void {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A component name must not be blank.');
        }
    }

    /**
     * @param int[] $componentIds
     * @return int[]
     */
    private function normalizeRelatedComponentIds(array $componentIds): array {
        $normalized = [];

        foreach ($componentIds as $componentId) {
            $componentId = (int) $componentId;

            if ($componentId <= 0) {
                throw new InvalidArgumentException('Related component IDs must be positive integers.');
            }

            $normalized[$componentId] = $componentId;
        }

        return array_values($normalized);
    }

    private function guardInheritanceDoesNotReferenceSelf(): void {
        if ($this->id === null) {
            return;
        }

        if ($this->parentComponentId === $this->id || in_array($this->id, $this->childComponentIds, true)) {
            throw new InvalidArgumentException('A component cannot inherit from itself.');
        }
    }

    private function guardInheritanceDoesNotConflict(): void {
        if ($this->parentComponentId !== null && in_array($this->parentComponentId, $this->childComponentIds, true)) {
            throw new InvalidArgumentException('A component cannot inherit from and be parent of the same component.');
        }
    }

    private function guardParentIdIsPositive(): void {
        if ($this->parentComponentId !== null && $this->parentComponentId <= 0) {
            throw new InvalidArgumentException('Related component IDs must be positive integers.');
        }
    }
}
