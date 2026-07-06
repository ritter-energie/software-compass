<?php

declare(strict_types=1);

namespace App\Presentation\ViewModel;

use App\Domain\Journey\JourneyStepComponent;

final readonly class JourneyStepAssignmentViewModel
{
    public function __construct(
        public JourneyStepComponent $assignment,
        public string $componentName,
    ) {}
}
