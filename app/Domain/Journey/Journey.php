<?php

declare(strict_types=1);

namespace App\Domain\Journey;

use InvalidArgumentException;

/**
 * A Customer Journey groups an ordered sequence of {@see JourneyStep}s that
 * together describe an end-to-end business process (e.g. "Order to
 * Delivery"), and the components that support each step.
 */
final class Journey
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $slug,
        private ?string $description,
        private ?int $ownerId,
        private int $statusId,
        private int $sortOrder,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A journey name must not be blank.');
        }
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function ownerId(): ?int
    {
        return $this->ownerId;
    }

    public function statusId(): int
    {
        return $this->statusId;
    }

    public function sortOrder(): int
    {
        return $this->sortOrder;
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A journey name must not be blank.');
        }

        $this->name = $name;
    }
}

