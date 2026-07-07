<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData;

use InvalidArgumentException;

enum ReferenceDataType: string {
    case COMPONENT_TYPE = 'component-types';
    case COMPONENT_STATUS = 'component-statuses';
    case CRITICALITY_LEVEL = 'criticality-levels';
    case ENVIRONMENT = 'environments';
    case DEPLOYMENT_LOCATION = 'deployment-locations';
    case DEPENDENCY_TYPE = 'dependency-types';
    case COMMUNICATION_PROTOCOL = 'communication-protocols';
    case DATA_OBJECT = 'data-objects';
    case TAG = 'tags';
    case TEAM = 'teams';

    public static function fromRoute(string $value): self {
        return self::tryFrom($value) ?? throw new InvalidArgumentException('flash.error.master_data_group_not_found');
    }

    public function table(): string {
        return match ($this) {
            self::COMPONENT_TYPE => 'component_types',
            self::COMPONENT_STATUS => 'component_statuses',
            self::CRITICALITY_LEVEL => 'criticality_levels',
            self::ENVIRONMENT => 'environments',
            self::DEPLOYMENT_LOCATION => 'deployment_locations',
            self::DEPENDENCY_TYPE => 'dependency_types',
            self::COMMUNICATION_PROTOCOL => 'communication_protocols',
            self::DATA_OBJECT => 'data_objects',
            self::TAG => 'tags',
            self::TEAM => 'teams',
        };
    }

    public function titleKey(): string {
        return match ($this) {
            self::COMPONENT_TYPE => 'master_data.component_types',
            self::COMPONENT_STATUS => 'master_data.component_statuses',
            self::CRITICALITY_LEVEL => 'master_data.criticality_levels',
            self::ENVIRONMENT => 'master_data.environments',
            self::DEPLOYMENT_LOCATION => 'master_data.deployment_locations',
            self::DEPENDENCY_TYPE => 'master_data.dependency_types',
            self::COMMUNICATION_PROTOCOL => 'master_data.communication_protocols',
            self::DATA_OBJECT => 'master_data.data_objects',
            self::TAG => 'master_data.tags',
            self::TEAM => 'master_data.teams',
        };
    }

    public function orderBy(): string {
        return match ($this) {
            self::COMPONENT_STATUS, self::CRITICALITY_LEVEL => 'sort_order',
            default => 'name',
        };
    }

    /**
     * @return ReferenceDataField[]
     */
    public function fields(): array {
        return match ($this) {
            self::COMPONENT_STATUS, self::CRITICALITY_LEVEL => [ReferenceDataField::NAME, ReferenceDataField::DESCRIPTION, ReferenceDataField::SORT_ORDER],
            self::DEPLOYMENT_LOCATION => [ReferenceDataField::NAME, ReferenceDataField::LOCATION_TYPE, ReferenceDataField::DESCRIPTION],
            self::DATA_OBJECT => [
                ReferenceDataField::NAME,
                ReferenceDataField::DESCRIPTION,
                ReferenceDataField::CONTAINS_PERSONAL_DATA,
                ReferenceDataField::CONTAINS_SENSITIVE_DATA,
            ],
            self::TAG => [ReferenceDataField::NAME],
            self::TEAM => [ReferenceDataField::NAME, ReferenceDataField::DESCRIPTION],
            default => [ReferenceDataField::NAME, ReferenceDataField::DESCRIPTION],
        };
    }
}
