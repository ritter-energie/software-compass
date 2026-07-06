<?php

declare(strict_types=1);

namespace App\Application\Dashboard;

final readonly class DashboardViewModel
{
    /**
     * @param array<string, int> $metrics
     * @param array<int, array<string, mixed>> $recentComponents
     * @param array<int, array<string, mixed>> $openReviews
     * @param array<int, array<string, mixed>> $incompleteComponents
     * @param array<int, array<string, mixed>> $criticalDependencies
     */
    public function __construct(
        public array $metrics,
        public array $recentComponents,
        public array $openReviews,
        public array $incompleteComponents = [],
        public array $criticalDependencies = [],
    ) {}
}
