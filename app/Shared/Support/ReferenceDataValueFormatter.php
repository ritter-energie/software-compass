<?php

declare(strict_types=1);

namespace App\Shared\Support;

final readonly class ReferenceDataValueFormatter
{
    public static function format(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? Translator::translate('common.yes') : Translator::translate('common.no');
        }

        return (string) $value;
    }
}
