<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Governance\GovernanceReview;
use App\Domain\Governance\GovernanceReviewRepository;
use DateTimeImmutable;

use function Tempest\Database\query;

final class MariaDbGovernanceReviewRepository implements GovernanceReviewRepository {
    use ResolvesLastInsertId;

    public function findById(int $id): ?GovernanceReview {
        $row = query('governance_reviews')->select()->whereField('id', $id)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function findByComponentId(int $componentId): ?GovernanceReview {
        $row = query('governance_reviews')->select()->whereField('component_id', $componentId)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function openReviews(): array {
        $rows = query('governance_reviews')
            ->select()
            ->whereIn('review_status', [
                GovernanceReview::STATUS_OPEN,
                GovernanceReview::STATUS_IN_PROGRESS,
                GovernanceReview::STATUS_NEEDS_CHANGES,
            ])
            ->orderBy('created_at')
            ->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function all(): array {
        $rows = query('governance_reviews')->select()->orderBy('created_at')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function save(GovernanceReview $review): GovernanceReview {
        $data = $this->toRow($review);

        if ($review->id() === null) {
            query('governance_reviews')->insert($data)->execute();

            return $this->findById($this->lastInsertId());
        }

        query('governance_reviews')->update(...$data)->whereField('id', $review->id())->execute();

        return $this->findById($review->id());
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): GovernanceReview {
        return new GovernanceReview(
            id: (int) $row['id'],
            componentId: (int) $row['component_id'],
            reviewerId: $row['reviewer_id'] !== null ? (int) $row['reviewer_id'] : null,
            reviewStatus: $row['review_status'],
            duplicateCheckDone: (bool) $row['duplicate_check_done'],
            interfaceCheckDone: (bool) $row['interface_check_done'],
            ownerCheckDone: (bool) $row['owner_check_done'],
            dataCheckDone: (bool) $row['data_check_done'],
            deploymentCheckDone: (bool) $row['deployment_check_done'],
            notes: $row['notes'],
            reviewedAt: $row['reviewed_at'] !== null ? new DateTimeImmutable((string) $row['reviewed_at']) : null,
        );
    }

    /** @return array<string, mixed> */
    private function toRow(GovernanceReview $review): array {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $row = [
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
            'updated_at' => $now,
        ];

        if ($review->id() === null) {
            $row['created_at'] = $now;
        }

        return $row;
    }
}
