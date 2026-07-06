<?php

declare(strict_types=1);

namespace App\Domain\Journey;

interface JourneyRepository
{
    public function findById(int $id): ?Journey;

    public function findBySlug(string $slug): ?Journey;

    /**
     * @return Journey[]
     */
    public function all(): array;

    public function save(Journey $journey): Journey;

    public function delete(int $id): void;

    /**
     * @return JourneyStep[]
     */
    public function stepsForJourney(int $journeyId): array;

    public function findStepById(int $stepId): ?JourneyStep;

    public function saveStep(JourneyStep $step): JourneyStep;

    public function deleteStep(int $stepId): void;

    /**
     * @return JourneyStepComponent[]
     */
    public function componentsForStep(int $stepId): array;

    public function findStepComponentById(int $stepComponentId): ?JourneyStepComponent;

    public function saveStepComponent(JourneyStepComponent $stepComponent): JourneyStepComponent;

    public function deleteStepComponent(int $stepComponentId): void;

    public function slugExists(string $slug, ?int $excludingId = null): bool;
}

