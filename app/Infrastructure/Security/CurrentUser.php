<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Shared\Support\LocaleSupport;

/**
 * Request-local identity resolved from session-based authentication.
 *
 * PHP request lifecycles are isolated, so this small context lets Application
 * Services resolve audit/reviewer person ids without coupling them to HTTP.
 */
final class CurrentUser
{
    private static ?int $userId = null;

    private static ?int $personId = null;

    private static string $locale = 'en';

    private static ?string $displayName = null;

    /** @var string[] */
    private static array $roles = [];

    /** @param string[] $roles */
    public static function authenticate(int $userId, ?int $personId, string $locale = 'en', array $roles = [], ?string $displayName = null): void
    {
        self::$userId = $userId;
        self::$personId = $personId;
        self::$locale = LocaleSupport::normalize($locale);
        self::$displayName = $displayName;
        self::$roles = array_values(array_unique(array_filter(array_map(static fn (mixed $role): string => strtolower(trim((string) $role)), $roles))));
    }

    public static function clear(): void
    {
        self::$userId = null;
        self::$personId = null;
        self::$locale = 'en';
        self::$displayName = null;
        self::$roles = [];
    }

    public static function userId(): ?int
    {
        return self::$userId;
    }

    public static function personId(): ?int
    {
        return self::$personId;
    }

    public static function locale(): string
    {
        return self::$locale;
    }

    public static function displayName(): ?string
    {
        return self::$displayName;
    }

    /** @return string[] */
    public static function roles(): array
    {
        return self::$roles;
    }

    public static function hasRole(string $role): bool
    {
        return in_array(strtolower(trim($role)), self::$roles, true);
    }
}

