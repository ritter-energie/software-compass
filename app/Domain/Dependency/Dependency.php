<?php

declare(strict_types=1);

namespace App\Domain\Dependency;

use InvalidArgumentException;

/**
 * Represents an interface / communication path between two components.
 *
 * Holds the fields and business rules this entity enforces.
 */
final class Dependency
{
    public const string DIRECTION_SOURCE_TO_TARGET = 'source_to_target';

    public const string DIRECTION_TARGET_TO_SOURCE = 'target_to_source';

    public function __construct(
        private ?int $id,
        private int $sourceComponentId,
        private int $targetComponentId,
        private int $dependencyTypeId,
        private ?int $protocolId,
        private int $statusId,
        private ?int $criticalityId,
        private ?int $ownerId,
        private ?int $ownerTeamId,
        private string $name,
        private ?string $description,
        private ?string $dataDescription,
        private ?string $frequency,
        private string $direction,
        private ?string $authenticationMethod,
        private ?string $documentationUrl,
        private ?string $technicalNotes,
        private bool $isBidirectional,
    ) {
        if ($sourceComponentId === $targetComponentId) {
            throw new InvalidArgumentException('A component cannot depend on itself.');
        }

        if (trim($name) === '') {
            throw new InvalidArgumentException('A dependency name must not be blank.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function sourceComponentId(): int
    {
        return $this->sourceComponentId;
    }

    public function targetComponentId(): int
    {
        return $this->targetComponentId;
    }

    public function dependencyTypeId(): int
    {
        return $this->dependencyTypeId;
    }

    public function protocolId(): ?int
    {
        return $this->protocolId;
    }

    public function statusId(): int
    {
        return $this->statusId;
    }

    public function criticalityId(): ?int
    {
        return $this->criticalityId;
    }

    public function ownerId(): ?int
    {
        return $this->ownerId;
    }

    public function ownerTeamId(): ?int
    {
        return $this->ownerTeamId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function dataDescription(): ?string
    {
        return $this->dataDescription;
    }

    public function frequency(): ?string
    {
        return $this->frequency;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public function authenticationMethod(): ?string
    {
        return $this->authenticationMethod;
    }

    public function documentationUrl(): ?string
    {
        return $this->documentationUrl;
    }

    public function technicalNotes(): ?string
    {
        return $this->technicalNotes;
    }

    public function isBidirectional(): bool
    {
        return $this->isBidirectional;
    }

    /**
     * A short, human-readable label combining the dependency name with its
     * data description, used in diagrams.
     */
    public function label(): string
    {
        return $this->dataDescription
            ? sprintf('%s / %s', $this->name, $this->dataDescription)
            : $this->name;
    }

    /**
     * A dependency is considered incomplete when it lacks an owner or a
     * description of the data it transports.
     */
    public function isIncomplete(): bool
    {
        return $this->ownerId === null && $this->ownerTeamId === null || $this->dataDescription === null || trim((string) $this->dataDescription) === '';
    }
}
