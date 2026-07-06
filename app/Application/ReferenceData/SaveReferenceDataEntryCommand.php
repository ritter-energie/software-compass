<?php

declare(strict_types=1);

namespace App\Application\ReferenceData;

use App\Domain\ReferenceData\ReferenceDataType;

final readonly class SaveReferenceDataEntryCommand
{
    public function __construct(
        public ReferenceDataType $type,
        public string $name,
        public ?string $description = null,
        public int $sortOrder = 0,
        public ?string $locationType = null,
        public bool $containsPersonalData = false,
        public bool $containsSensitiveData = false,
    ) {}
}

