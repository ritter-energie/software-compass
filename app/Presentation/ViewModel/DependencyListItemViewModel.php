<?php

declare(strict_types=1);

namespace App\Presentation\ViewModel;

final readonly class DependencyListItemViewModel {
    public function __construct(
        public int $id,
        public string $name,
        public string $sourceComponentName,
        public string $targetComponentName,
        public ?string $dataDescription,
        public ?string $frequency,
        public bool $isIncomplete,
    ) {}
}
