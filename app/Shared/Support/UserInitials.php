<?php

declare(strict_types=1);

namespace App\Shared\Support;

final readonly class UserInitials
{
    public static function fromName(?string $name): string
    {
        $normalized = trim((string) $name);
        if ($normalized === '') {
            return '?';
        }

        $matches = [];
        preg_match_all('/[\p{L}\p{N}]+/u', $normalized, $matches);
        $tokens = $matches[0];
        if ($tokens === []) {
            return '?';
        }

        $first = self::characterAt($tokens[0], 0);
        $second = count($tokens) > 1
            ? self::characterAt($tokens[(int) ceil(count($tokens) / 2)], 0)
            : self::characterAt($tokens[0], 1);

        return mb_strtoupper(mb_substr($first . $second, 0, 2), 'UTF-8');
    }

    private static function characterAt(string $token, int $position): string
    {
        return mb_substr($token, $position, 1, 'UTF-8');
    }
}
