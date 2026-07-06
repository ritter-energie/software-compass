<?php
declare(strict_types=1);

namespace App\Application\Dependency;

final readonly class CreateDependencyCommand
{
    public function __construct(
        public int $sourceComponentId,
        public int $targetComponentId,
        public int $dependencyTypeId,
        public ?int $protocolId,
        public int $statusId,
        public ?int $criticalityId,
        public ?int $ownerId,
        public ?int $ownerTeamId,
        public string $name,
        public ?string $description,
        public ?string $dataDescription,
        public ?string $frequency,
        public string $direction,
        public ?string $authenticationMethod,
        public ?string $documentationUrl,
        public ?string $technicalNotes,
        public bool $isBidirectional,
    ) {}
}
