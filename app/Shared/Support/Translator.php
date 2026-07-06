<?php

declare(strict_types=1);

namespace App\Shared\Support;

use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Infrastructure\Security\CurrentUser;
use Tempest\Intl\Locale;
use Tempest\Intl\Translator as TempestTranslator;

use function Tempest\get;

/**
 * Small app-level facade over Tempest Intl so views can translate for the
 * authenticated user's stored locale without duplicating locale resolution.
 */
final class Translator
{
    /** @return array<string, string> */
    public static function supportedLocales(): array
    {
        return ['en' => self::translate('language.english'), 'de' => self::translate('language.german')];
    }

    public static function locale(): string
    {
        if (CurrentUser::userId() !== null) {
            return CurrentUser::locale();
        }

        return get(AppSettingsRepository::class)->defaultLocale();
    }

    public static function translate(string $key, mixed ...$arguments): string
    {
        return get(TempestTranslator::class)->translateForLocale(self::tempestLocale(), $key, ...$arguments);
    }

    private static function tempestLocale(): Locale
    {
        return match (self::locale()) {
            'de' => Locale::GERMAN,
            default => Locale::ENGLISH,
        };
    }
}
