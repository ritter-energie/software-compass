<?php

declare(strict_types=1);

namespace App\Domain\ReferenceData;

enum ReferenceDataFieldType: string
{
    case TEXT = 'text';
    case TEXTAREA = 'textarea';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
}

