<?php

declare(strict_types=1);

namespace App\Domain\Governance;

interface GovernanceReviewRepository
{
    public function findById(int $id): ?GovernanceReview;

    public function findByComponentId(int $componentId): ?GovernanceReview;

    /**
     * @return GovernanceReview[]
     */
    public function openReviews(): array;

    /**
     * @return GovernanceReview[]
     */
    public function all(): array;

    public function save(GovernanceReview $review): GovernanceReview;
}

