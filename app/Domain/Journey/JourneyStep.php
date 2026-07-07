<?php

declare(strict_types=1);

namespace App\Domain\Journey;

use InvalidArgumentException;

/**
 * A single step within a {@see Journey}, in `sort_order`. Components are
 * attached to steps via `journey_step_components` with a role (see
 * `JourneyStepComponent`).
 */
final class JourneyStep {
    public function __construct(
        private ?int $id,
        private int $journeyId,
        private string $name,
        private ?string $description,
        private int $sortOrder,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A journey step name must not be blank.');
        }
    }

    public function id(): ?int {
        return $this->id;
    }

    public function journeyId(): int {
        return $this->journeyId;
    }

    public function name(): string {
        return $this->name;
    }

    public function description(): ?string {
        return $this->description;
    }

    public function sortOrder(): int {
        return $this->sortOrder;
    }
}
