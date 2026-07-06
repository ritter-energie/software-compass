<?php
declare(strict_types=1);

namespace App\Application\Component;

use App\Domain\Component\Component;
use App\Domain\Dependency\Dependency;
use App\Domain\Governance\GovernanceReview;

final readonly class ComponentDetailViewModel
{
    /**
     * @param Dependency[] $incomingDependencies
     * @param Dependency[] $outgoingDependencies
     * @param Component[] $parentComponents
     * @param Component[] $childComponents
     * @param string[] $warnings
     */
    public function __construct(
        public Component $component,
        public array $incomingDependencies,
        public array $outgoingDependencies,
        public array $parentComponents,
        public array $childComponents,
        public ?GovernanceReview $governanceReview,
        public array $warnings,
        public string $mermaidDiagram,
    ) {}
}
