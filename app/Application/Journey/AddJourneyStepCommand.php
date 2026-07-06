<?php
declare(strict_types=1);

namespace App\Application\Journey;

final readonly class AddJourneyStepCommand
{
    public function __construct(
        public int $journeyId,
        public string $name,
        public ?string $description,
        public int $sortOrder,
    ) {}
}
