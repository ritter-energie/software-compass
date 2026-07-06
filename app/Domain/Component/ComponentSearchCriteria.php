<?php

declare(strict_types=1);

namespace App\Domain\Component;

/**
 * Filter/search parameters for the component list.
 */
final readonly class ComponentSearchCriteria
{
    public function __construct(
        public ?string $query = null,
        public ?int $componentTypeId = null,
        public ?int $statusId = null,
        public ?int $criticalityId = null,
        public ?int $ownerId = null,
        public ?int $ownerTeamId = null,
        public ?int $environmentId = null,
        public ?bool $isExternal = null,
        public ?int $tagId = null,
    ) {}
}
