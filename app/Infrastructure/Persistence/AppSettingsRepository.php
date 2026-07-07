<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Shared\Support\LocaleSupport;
use DateTimeImmutable;

use function Tempest\Database\query;

/** Stores small application-wide key/value settings. */
final class AppSettingsRepository {
    private const string DEFAULT_LOCALE_KEY = 'default_locale';

    public function get(string $key): ?string {
        $row = query('app_settings')->select()->whereField('setting_key', $key)->first();

        if ($row === null) {
            return null;
        }

        $value = $row['setting_value'] ?? null;

        return $value !== null ? (string) $value : null;
    }

    public function set(string $key, ?string $value): void {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $existing = query('app_settings')->select()->whereField('setting_key', $key)->first();

        if ($existing !== null) {
            query('app_settings')
                ->update(setting_value: $value, updated_at: $now)
                ->whereField('id', (int) $existing['id'])
                ->execute();

            return;
        }

        query('app_settings')->insert([
            'setting_key' => $key,
            'setting_value' => $value,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();
    }

    public function defaultLocale(): string {
        return LocaleSupport::normalize($this->get(self::DEFAULT_LOCALE_KEY));
    }

    public function setDefaultLocale(string $locale): void {
        $this->set(self::DEFAULT_LOCALE_KEY, LocaleSupport::normalize($locale));
    }
}
