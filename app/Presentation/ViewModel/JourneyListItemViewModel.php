<?php

declare(strict_types=1);

namespace App\Presentation\ViewModel;

final readonly class JourneyListItemViewModel
{
    public function __construct(
        public int $id,
        public string $name,
        public string $ownerName,
        public int $statusId,
        public int $sortOrder,
    ) {}
}
