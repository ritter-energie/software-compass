<?php
declare(strict_types=1);
namespace App\Application\Governance;
use App\Application\Audit\AuditLogger;
use App\Domain\Governance\GovernanceReview;
use App\Domain\Governance\GovernanceReviewRepository;
use RuntimeException;
final readonly class GovernanceService
{
    public function __construct(private GovernanceReviewRepository $reviews, private AuditLogger $audit) {}
    public function createReviewForComponent(int $componentId): GovernanceReview
    {
        if ($existing = $this->reviews->findByComponentId($componentId)) {
            return $existing;
        }
        $review = $this->reviews->save(new GovernanceReview(
            id: null,
            componentId: $componentId,
            reviewerId: null,
            reviewStatus: GovernanceReview::STATUS_OPEN,
            duplicateCheckDone: false,
            interfaceCheckDone: false,
            ownerCheckDone: false,
            dataCheckDone: false,
            deploymentCheckDone: false,
            notes: null,
            reviewedAt: null,
        ));
        $this->audit->log('governance_review', (int) $review->id(), 'created', null, $this->snapshot($review));

        return $review;
    }
    public function updateReview(UpdateGovernanceReviewCommand $command): GovernanceReview
    {
        $review = $this->reviews->findById($command->reviewId) ?? throw new RuntimeException('Review not found.');
        $old = $this->snapshot($review);
        $review->updateChecklist(
            duplicateCheckDone: $command->duplicateCheckDone,
            interfaceCheckDone: $command->interfaceCheckDone,
            ownerCheckDone: $command->ownerCheckDone,
            dataCheckDone: $command->dataCheckDone,
            deploymentCheckDone: $command->deploymentCheckDone,
            notes: $command->notes,
        );
        $updated = $this->reviews->save($review);
        $this->audit->log('governance_review', (int) $updated->id(), 'updated', $old, $this->snapshot($updated));

        return $updated;
    }
    public function approve(int $reviewId, int $reviewerId, ?string $notes): void
    {
        $review = $this->reviews->findById($reviewId) ?? throw new RuntimeException('Review not found.');
        $old = $this->snapshot($review);
        $review->approve($reviewerId, $notes);
        $updated = $this->reviews->save($review);
        $this->audit->log('governance_review', (int) $updated->id(), 'approved', $old, $this->snapshot($updated));
    }
    public function reject(int $reviewId, int $reviewerId, ?string $notes): void
    {
        $review = $this->reviews->findById($reviewId) ?? throw new RuntimeException('Review not found.');
        $old = $this->snapshot($review);
        $review->reject($reviewerId, $notes);
        $updated = $this->reviews->save($review);
        $this->audit->log('governance_review', (int) $updated->id(), 'rejected', $old, $this->snapshot($updated));
    }
    /** @return GovernanceReview[] */
    public function openReviews(): array
    {
        return $this->reviews->openReviews();
    }

    /** @return array<string, mixed> */
    private function snapshot(GovernanceReview $review): array
    {
        return [
            'id' => $review->id(),
            'component_id' => $review->componentId(),
            'reviewer_id' => $review->reviewerId(),
            'review_status' => $review->reviewStatus(),
            'duplicate_check_done' => $review->duplicateCheckDone(),
            'interface_check_done' => $review->interfaceCheckDone(),
            'owner_check_done' => $review->ownerCheckDone(),
            'data_check_done' => $review->dataCheckDone(),
            'deployment_check_done' => $review->deploymentCheckDone(),
            'notes' => $review->notes(),
            'reviewed_at' => $review->reviewedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
