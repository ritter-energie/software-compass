<?php

declare(strict_types=1);

namespace App\Shared\Support;

/** Centralizes supported UI locale handling. */
final readonly class LocaleSupport {
    public const string FALLBACK = 'en';

    /** @return string[] */
    public static function supportedCodes(): array {
        return ['en', 'de'];
    }

    public static function isSupported(string $locale): bool {
        return in_array($locale, self::supportedCodes(), true);
    }

    public static function normalize(?string $locale, string $fallback = self::FALLBACK): string {
        $normalizedFallback = self::isSupported($fallback) ? $fallback : self::FALLBACK;
        $normalizedLocale = strtolower(trim((string) $locale));

        return self::isSupported($normalizedLocale) ? $normalizedLocale : $normalizedFallback;
    }
}
