<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Governance\GovernanceReview;
use PHPUnit\Framework\TestCase;

final class GovernanceReviewTest extends TestCase {
    public function test_checklist_update_moves_review_from_open_to_in_progress(): void {
        $review = $this->review(status: GovernanceReview::STATUS_OPEN);

        $review->updateChecklist(
            duplicateCheckDone: true,
            interfaceCheckDone: false,
            ownerCheckDone: false,
            dataCheckDone: false,
            deploymentCheckDone: false,
            notes: 'Checked duplicates',
        );

        $this->assertSame(GovernanceReview::STATUS_IN_PROGRESS, $review->reviewStatus());
        $this->assertTrue($review->isOpen());
        $this->assertTrue($review->duplicateCheckDone());
    }

    public function test_approve_sets_terminal_status_reviewer_and_timestamp(): void {
        $review = $this->review(status: GovernanceReview::STATUS_IN_PROGRESS);

        $review->approve(reviewerId: 42, notes: 'Looks good');

        $this->assertSame(GovernanceReview::STATUS_APPROVED, $review->reviewStatus());
        $this->assertSame(42, $review->reviewerId());
        $this->assertSame('Looks good', $review->notes());
        $this->assertNotNull($review->reviewedAt());
        $this->assertFalse($review->isOpen());
    }

    public function test_reject_sets_terminal_status_reviewer_and_timestamp(): void {
        $review = $this->review(status: GovernanceReview::STATUS_IN_PROGRESS);

        $review->reject(reviewerId: 7, notes: 'Owner missing');

        $this->assertSame(GovernanceReview::STATUS_REJECTED, $review->reviewStatus());
        $this->assertSame(7, $review->reviewerId());
        $this->assertSame('Owner missing', $review->notes());
        $this->assertNotNull($review->reviewedAt());
        $this->assertFalse($review->isOpen());
    }

    private function review(string $status): GovernanceReview {
        return new GovernanceReview(
            id: 1,
            componentId: 10,
            reviewerId: null,
            reviewStatus: $status,
            duplicateCheckDone: false,
            interfaceCheckDone: false,
            ownerCheckDone: false,
            dataCheckDone: false,
            deploymentCheckDone: false,
            notes: null,
            reviewedAt: null,
        );
    }
}
