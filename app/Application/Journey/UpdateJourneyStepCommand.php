<?php

declare(strict_types=1);

namespace App\Application\Journey;

final readonly class UpdateJourneyStepCommand {
    public function __construct(
        public int $id,
        public int $journeyId,
        public string $name,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
