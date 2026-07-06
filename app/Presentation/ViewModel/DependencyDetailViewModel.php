<?php

declare(strict_types=1);

namespace App\Presentation\ViewModel;

use App\Domain\Dependency\Dependency;

final readonly class DependencyDetailViewModel
{
    public function __construct(
        public Dependency $dependency,
        public string $sourceComponentName,
        public string $targetComponentName,
        public string $ownerName,
        public string $ownerTeamName,
    ) {}
}
