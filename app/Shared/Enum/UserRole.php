<?php

declare(strict_types=1);

namespace App\Shared\Enum;

/**
 * Reserved application roles prepared for later authorization enforcement.
 */
enum UserRole: string {
    case VIEWER = 'viewer';
    case EDITOR = 'editor';
    case ARCHITECT = 'architect';
    case ADMIN = 'admin';

    /** @return list<string> */
    public static function values(): array {
        return array_map(static fn (self $role): string => $role->value, self::cases());
    }
}
