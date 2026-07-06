<?php

declare(strict_types=1);

namespace App\Domain\Journey;

/**
 * Associates a component to a journey step with a role (e.g. `leading`,
 * `supporting`, `data_source`).
 */
final class JourneyStepComponent
{
    public const string ROLE_LEADING = 'leading';

    public const string ROLE_SUPPORTING = 'supporting';

    public const string ROLE_DATA_SOURCE = 'data_source';

    public const string ROLE_TARGET_SYSTEM = 'target_system';

    public const string ROLE_MANUAL = 'manual';

    public const string ROLE_REPORTING = 'reporting';

    /**
     * @return string[]
     */
    public static function validRoles(): array
    {
        return [
            self::ROLE_LEADING,
            self::ROLE_SUPPORTING,
            self::ROLE_DATA_SOURCE,
            self::ROLE_TARGET_SYSTEM,
            self::ROLE_MANUAL,
            self::ROLE_REPORTING,
        ];
    }

    public function __construct(
        private ?int $id,
        private int $journeyStepId,
        private int $componentId,
        private string $roleInStep,
        private ?string $notes,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function journeyStepId(): int
    {
        return $this->journeyStepId;
    }

    public function componentId(): int
    {
        return $this->componentId;
    }

    public function roleInStep(): string
    {
        return $this->roleInStep;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }
}

