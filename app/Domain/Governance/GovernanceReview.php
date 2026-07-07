<?php

declare(strict_types=1);

namespace App\Domain\Governance;

use DateTimeImmutable;

/**
 * Tracks the mandatory governance checklist for a new or changed component.
 */
final class GovernanceReview {
    public const string STATUS_OPEN = 'open';

    public const string STATUS_IN_PROGRESS = 'in_progress';

    public const string STATUS_APPROVED = 'approved';

    public const string STATUS_REJECTED = 'rejected';

    public const string STATUS_NEEDS_CHANGES = 'needs_changes';

    public function __construct(
        private ?int $id,
        private int $componentId,
        private ?int $reviewerId,
        private string $reviewStatus,
        private bool $duplicateCheckDone,
        private bool $interfaceCheckDone,
        private bool $ownerCheckDone,
        private bool $dataCheckDone,
        private bool $deploymentCheckDone,
        private ?string $notes,
        private ?DateTimeImmutable $reviewedAt,
    ) {}

    public function id(): ?int {
        return $this->id;
    }

    public function componentId(): int {
        return $this->componentId;
    }

    public function reviewerId(): ?int {
        return $this->reviewerId;
    }

    public function reviewStatus(): string {
        return $this->reviewStatus;
    }

    public function duplicateCheckDone(): bool {
        return $this->duplicateCheckDone;
    }

    public function interfaceCheckDone(): bool {
        return $this->interfaceCheckDone;
    }

    public function ownerCheckDone(): bool {
        return $this->ownerCheckDone;
    }

    public function dataCheckDone(): bool {
        return $this->dataCheckDone;
    }

    public function deploymentCheckDone(): bool {
        return $this->deploymentCheckDone;
    }

    public function notes(): ?string {
        return $this->notes;
    }

    public function reviewedAt(): ?DateTimeImmutable {
        return $this->reviewedAt;
    }

    public function isOpen(): bool {
        return in_array($this->reviewStatus, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS, self::STATUS_NEEDS_CHANGES], true);
    }

    public function approve(int $reviewerId, ?string $notes): void {
        $this->reviewerId = $reviewerId;
        $this->reviewStatus = self::STATUS_APPROVED;
        $this->notes = $notes;
        $this->reviewedAt = new DateTimeImmutable();
    }

    public function reject(int $reviewerId, ?string $notes): void {
        $this->reviewerId = $reviewerId;
        $this->reviewStatus = self::STATUS_REJECTED;
        $this->notes = $notes;
        $this->reviewedAt = new DateTimeImmutable();
    }

    public function updateChecklist(
        bool $duplicateCheckDone,
        bool $interfaceCheckDone,
        bool $ownerCheckDone,
        bool $dataCheckDone,
        bool $deploymentCheckDone,
        ?string $notes,
    ): void {
        $this->duplicateCheckDone = $duplicateCheckDone;
        $this->interfaceCheckDone = $interfaceCheckDone;
        $this->ownerCheckDone = $ownerCheckDone;
        $this->dataCheckDone = $dataCheckDone;
        $this->deploymentCheckDone = $deploymentCheckDone;
        $this->notes = $notes;

        if ($this->reviewStatus === self::STATUS_OPEN) {
            $this->reviewStatus = self::STATUS_IN_PROGRESS;
        }
    }
}
