<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData;

final readonly class ReferenceDataEntry {
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description = null,
        public int $sortOrder = 0,
        public ?string $locationType = null,
        public bool $containsPersonalData = false,
        public bool $containsSensitiveData = false,
    ) {}

    public function value(ReferenceDataField $field): string|int|bool|null {
        return match ($field) {
            ReferenceDataField::NAME => $this->name,
            ReferenceDataField::DESCRIPTION => $this->description,
            ReferenceDataField::SORT_ORDER => $this->sortOrder,
            ReferenceDataField::LOCATION_TYPE => $this->locationType,
            ReferenceDataField::CONTAINS_PERSONAL_DATA => $this->containsPersonalData,
            ReferenceDataField::CONTAINS_SENSITIVE_DATA => $this->containsSensitiveData,
        };
    }
}
