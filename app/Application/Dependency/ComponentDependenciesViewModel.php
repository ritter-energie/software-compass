<?php
declare(strict_types=1);

namespace App\Application\Dependency;

use App\Domain\Dependency\Dependency;

final readonly class ComponentDependenciesViewModel
{
    /** @param Dependency[] $incoming @param Dependency[] $outgoing */
    public function __construct(
        public int $componentId,
        public array $incoming,
        public array $outgoing,
    ) {}
}
