<?php

declare(strict_types=1);

namespace App\Application\ReferenceData;

use App\Domain\ReferenceData\ReferenceDataEntry;
use App\Domain\ReferenceData\ReferenceDataField;
use App\Domain\ReferenceData\ReferenceDataType;
use App\Infrastructure\Persistence\LookupRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final readonly class ReferenceDataService
{
    public function __construct(
        private LookupRepository $lookups,
    ) {}

    /**
     * @return ReferenceDataType[]
     */
    public function types(): array
    {
        return ReferenceDataType::cases();
    }

    /**
     * @return ReferenceDataEntry[]
     */
    public function entries(ReferenceDataType $type): array
    {
        return array_map(
            $this->entryFromRow(...),
            $this->lookups->allFrom($type->table(), $type->orderBy()),
        );
    }

    public function entry(ReferenceDataType $type, int $id): ReferenceDataEntry
    {
        $row = $this->lookups->findIn($type->table(), $id);

        return $row !== null
            ? $this->entryFromRow($row)
            : throw new RuntimeException('flash.error.master_data_entry_not_found');
    }

    public function create(SaveReferenceDataEntryCommand $command): int
    {
        return $this->lookups->insertInto($command->type->table(), $this->rowFromCommand($command));
    }

    public function update(int $id, SaveReferenceDataEntryCommand $command): void
    {
        $this->lookups->updateIn($command->type->table(), $id, $this->rowFromCommand($command));
    }

    public function delete(ReferenceDataType $type, int $id): void
    {
        try {
            $this->lookups->deleteFrom($type->table(), $id);
        } catch (Throwable) {
            throw new InvalidArgumentException('flash.error.master_data_in_use');
        }
    }

    /**
     * @param array<string, mixed> $row
     */
    private function entryFromRow(array $row): ReferenceDataEntry
    {
        return new ReferenceDataEntry(
            id: (int) $row['id'],
            name: (string) $row['name'],
            description: $row['description'] ?? null,
            sortOrder: (int) ($row['sort_order'] ?? 0),
            locationType: $row['location_type'] ?? null,
            containsPersonalData: (bool) ($row['contains_personal_data'] ?? false),
            containsSensitiveData: (bool) ($row['contains_sensitive_data'] ?? false),
        );
    }

    /** @return array<string, mixed> */
    private function rowFromCommand(SaveReferenceDataEntryCommand $command): array
    {
        $this->validate($command);
        $row = [];

        foreach ($command->type->fields() as $field) {
            $row[$field->value] = match ($field) {
                ReferenceDataField::NAME => $command->name,
                ReferenceDataField::DESCRIPTION => $command->description,
                ReferenceDataField::SORT_ORDER => $command->sortOrder,
                ReferenceDataField::LOCATION_TYPE => $command->locationType,
                ReferenceDataField::CONTAINS_PERSONAL_DATA => $command->containsPersonalData,
                ReferenceDataField::CONTAINS_SENSITIVE_DATA => $command->containsSensitiveData,
            };
        }

        return $row;
    }

    private function validate(SaveReferenceDataEntryCommand $command): void
    {
        foreach ($command->type->fields() as $field) {
            if (! $field->isRequired()) {
                continue;
            }

            $value = match ($field) {
                ReferenceDataField::NAME => $command->name,
                ReferenceDataField::LOCATION_TYPE => $command->locationType,
                default => null,
            };

            if ($value === null || trim($value) === '') {
                throw new InvalidArgumentException('flash.error.required_fields');
            }
        }
    }
}
