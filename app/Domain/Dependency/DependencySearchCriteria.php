<?php

declare(strict_types=1);

namespace App\Domain\Dependency;

final readonly class DependencySearchCriteria
{
    public function __construct(
        public ?string $query = null,
        public ?int $sourceComponentId = null,
        public ?int $targetComponentId = null,
        public ?int $dependencyTypeId = null,
        public ?int $protocolId = null,
        public ?int $statusId = null,
        public ?int $criticalityId = null,
        public ?int $ownerId = null,
        public ?int $dataObjectId = null,
    ) {}
}

