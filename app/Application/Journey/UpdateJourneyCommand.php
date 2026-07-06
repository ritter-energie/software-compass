<?php

declare(strict_types=1);

namespace App\Application\Journey;

final readonly class UpdateJourneyCommand
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public ?int $ownerId,
        public ?int $ownerTeamId,
        public int $statusId,
        public int $sortOrder = 0,
    ) {}
}
