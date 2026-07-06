<?php

declare(strict_types=1);

namespace App\Application\Diagram;

/** Filter object for component overview diagrams. */
final readonly class ComponentDiagramFilter
{
    public function __construct(
        public ?int $componentId = null,
        public ?int $statusId = null,
        public ?int $criticalityId = null,
        public ?int $componentTypeId = null,
        public ?int $ownerId = null,
        public int $maxNodes = 80,
    ) {}
}

