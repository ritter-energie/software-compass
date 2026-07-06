<?php

declare(strict_types=1);

namespace App\Domain\Dependency;

interface DependencyRepository
{
    public function findById(int $id): ?Dependency;

    /**
     * @return Dependency[]
     */
    public function findByComponentId(int $componentId): array;

    /**
     * @return Dependency[]
     */
    public function findIncomingForComponent(int $componentId): array;

    /**
     * @return Dependency[]
     */
    public function findOutgoingForComponent(int $componentId): array;

    /**
     * @return Dependency[]
     */
    public function search(DependencySearchCriteria $criteria): array;

    /**
     * @return Dependency[]
     */
    public function all(): array;

    public function save(Dependency $dependency): Dependency;

    public function delete(int $id): void;
}

