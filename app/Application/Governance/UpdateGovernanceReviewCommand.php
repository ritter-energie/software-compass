<?php
declare(strict_types=1);

namespace App\Application\Governance;

final readonly class UpdateGovernanceReviewCommand {
    public function __construct(
        public int $reviewId,
        public bool $duplicateCheckDone,
        public bool $interfaceCheckDone,
        public bool $ownerCheckDone,
        public bool $dataCheckDone,
        public bool $deploymentCheckDone,
        public ?string $notes,
    ) {}
}
