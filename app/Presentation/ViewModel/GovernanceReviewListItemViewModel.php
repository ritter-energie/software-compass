<?php

declare(strict_types=1);

namespace App\Presentation\ViewModel;

use App\Domain\Governance\GovernanceReview;

final readonly class GovernanceReviewListItemViewModel {
    public function __construct(
        public GovernanceReview $review,
        public string $componentName,
        public string $checksDoneLabel,
    ) {}
}
