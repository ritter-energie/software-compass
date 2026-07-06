<?php

declare(strict_types=1);

namespace App\Domain\Component;

/**
 * Repository interface for {@see Component} persistence, implemented in
 * `App\Infrastructure\Persistence\MariaDbComponentRepository`.
 *
 * The domain layer only depends on this interface, never on the concrete
 * database implementation (dependency inversion).
 */
interface ComponentRepository
{
    public function findById(int $id): ?Component;

    public function findBySlug(string $slug): ?Component;

    /**
     * @return Component[]
     */
    public function search(ComponentSearchCriteria $criteria): array;

    /**
     * @return Component[]
     */
    public function all(): array;

    /**
     * @return Component[]
     */
    public function parentsOf(int $componentId): array;

    /**
     * @return Component[]
     */
    public function childrenOf(int $componentId): array;

    public function save(Component $component): Component;

    public function delete(int $id): void;

    /**
     * Returns true if any other component already uses the given slug.
     */
    public function slugExists(string $slug, ?int $excludingId = null): bool;
}
