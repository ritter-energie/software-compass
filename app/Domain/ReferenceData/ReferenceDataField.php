<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData;

enum ReferenceDataField: string {
    case NAME = 'name';
    case DESCRIPTION = 'description';
    case SORT_ORDER = 'sort_order';
    case LOCATION_TYPE = 'location_type';
    case CONTAINS_PERSONAL_DATA = 'contains_personal_data';
    case CONTAINS_SENSITIVE_DATA = 'contains_sensitive_data';

    public function labelKey(): string {
        return match ($this) {
            self::NAME => 'form.name_required',
            self::DESCRIPTION => 'form.description',
            self::SORT_ORDER => 'form.sort_order',
            self::LOCATION_TYPE => 'master_data.location_type',
            self::CONTAINS_PERSONAL_DATA => 'master_data.personal_data',
            self::CONTAINS_SENSITIVE_DATA => 'master_data.sensitive_data',
        };
    }

    public function type(): ReferenceDataFieldType {
        return match ($this) {
            self::DESCRIPTION => ReferenceDataFieldType::TEXTAREA,
            self::SORT_ORDER => ReferenceDataFieldType::NUMBER,
            self::CONTAINS_PERSONAL_DATA, self::CONTAINS_SENSITIVE_DATA => ReferenceDataFieldType::BOOLEAN,
            default => ReferenceDataFieldType::TEXT,
        };
    }

    public function isRequired(): bool {
        return in_array($this, [self::NAME, self::LOCATION_TYPE], true);
    }
}
