<?php

declare(strict_types=1);

namespace App\Application\Dashboard;

use App\Domain\Governance\GovernanceReview;

use function Tempest\Database\query;

/** Builds dashboard metrics and short lists for architecture quality checks. */
final readonly class DashboardService
{
    public function buildDashboard(): DashboardViewModel
    {
        $activeStatusId = $this->idByName('component_statuses', 'Active');
        $replacementPlannedStatusId = $this->idByName('component_statuses', 'Replacement Planned');
        $businessCriticalId = $this->idByName('criticality_levels', 'Business Critical');
        $highCriticalityId = $this->idByName('criticality_levels', 'High');

        $metrics = [
            'components_total' => $this->count('components'),
            'active_components' => $activeStatusId ? $this->countWhere('components', 'status_id', $activeStatusId) : 0,
            'dependencies_total' => $this->count('dependencies'),
            'critical_dependencies' => $this->countCriticalDependencies($businessCriticalId, $highCriticalityId),
            'components_without_business_owner' => $this->countMissingOwner('components', 'business_owner_id', 'business_owner_team_id'),
            'components_without_technical_owner' => $this->countMissingOwner('components', 'technical_owner_id', 'technical_owner_team_id'),
            'dependencies_without_owner' => $this->countMissingOwner('dependencies', 'owner_id', 'owner_team_id'),
            'dependencies_without_documentation' => $this->countWhereNull('dependencies', 'documentation_url'),
            'open_reviews' => count($this->openReviewRows()),
            'replacement_planned_components' => $replacementPlannedStatusId ? $this->countWhere('components', 'status_id', $replacementPlannedStatusId) : 0,
        ];

        return new DashboardViewModel(
            metrics: $metrics,
            recentComponents: query('components')->select()->orderBy('updated_at')->limit(10)->all(),
            openReviews: $this->openReviewRows(),
            incompleteComponents: $this->incompleteComponentRows(),
            criticalDependencies: $this->criticalDependencyRows($businessCriticalId, $highCriticalityId),
        );
    }

    private function count(string $table): int
    {
        return query($table)->count()->execute();
    }

    private function countWhere(string $table, string $field, int $value): int
    {
        return query($table)->count()->whereField($field, $value)->execute();
    }

    private function countWhereNull(string $table, string $field): int
    {
        return query($table)->count()->whereNull($field)->execute();
    }

    private function countMissingOwner(string $table, string $personField, string $teamField): int
    {
        return query($table)
            ->count()
            ->whereNull($personField)
            ->whereNull($teamField)
            ->execute();
    }

    private function idByName(string $table, string $name): ?int
    {
        $row = query($table)->select()->whereField('name', $name)->first();

        return $row ? (int) $row['id'] : null;
    }

    private function countCriticalDependencies(?int ...$criticalityIds): int
    {
        $ids = array_values(array_filter($criticalityIds));
        if ($ids === []) {
            return 0;
        }

        return query('dependencies')->count()->whereIn('criticality_id', $ids)->execute();
    }

    /** @return array<int, array<string, mixed>> */
    private function openReviewRows(): array
    {
        return query('governance_reviews')
            ->select()
            ->whereIn('review_status', [
                GovernanceReview::STATUS_OPEN,
                GovernanceReview::STATUS_IN_PROGRESS,
                GovernanceReview::STATUS_NEEDS_CHANGES,
            ])
            ->orderBy('created_at')
            ->limit(10)
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function incompleteComponentRows(): array
    {
        $rows = query('components')->select()->orderBy('updated_at')->all();

        return array_slice(
            array_values(array_filter(
                $rows,
                static fn (array $row): bool => (
                    $row['business_owner_id'] === null
                    && $row['business_owner_team_id'] === null
                    || $row['technical_owner_id'] === null
                    && $row['technical_owner_team_id'] === null
                    || $row['purpose'] === null
                    || trim((string) $row['purpose']) === ''
                    || $row['deployment_location_id'] === null
                    || $row['environment_id'] === null
                ),
            )),
            0,
            10,
        );
    }

    /** @return array<int, array<string, mixed>> */
    private function criticalDependencyRows(?int ...$criticalityIds): array
    {
        $ids = array_values(array_filter($criticalityIds));
        if ($ids === []) {
            return [];
        }

        return query('dependencies')->select()->whereIn('criticality_id', $ids)->orderBy('updated_at')->limit(10)->all();
    }
}
