<?php
declare(strict_types=1);

namespace App\Application\Component;

use DateTimeImmutable;

final readonly class UpdateComponentCommand
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $shortName,
        public int $componentTypeId,
        public int $statusId,
        public ?int $criticalityId,
        public ?int $businessOwnerId,
        public ?int $businessOwnerTeamId,
        public ?int $technicalOwnerId,
        public ?int $technicalOwnerTeamId,
        public ?int $deploymentLocationId,
        public ?int $environmentId,
        public ?string $projectName,
        public ?DateTimeImmutable $startedOn,
        public ?string $purpose,
        public ?string $description,
        public ?string $documentationUrl,
        public ?string $repositoryUrl,
        public ?string $vendor,
        public ?string $lifecycleNotes,
        public bool $isExternal,
        /** @var int[] */
        public array $parentComponentIds = [],
        /** @var int[] */
        public array $childComponentIds = [],
    ) {}
}
