<?php
declare(strict_types=1);

namespace App\Application\Journey;

final readonly class CreateJourneyCommand
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?int $ownerId,
        public int $statusId,
        public int $sortOrder = 0,
    ) {}
}
