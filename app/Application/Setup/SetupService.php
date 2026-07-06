<?php

declare(strict_types=1);

namespace App\Application\Setup;

use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Shared\Enum\UserRole;
use App\Shared\Support\LocaleSupport;
use DateTimeImmutable;
use RuntimeException;

use function Tempest\Database\query;

/** Handles one-time first-run initialization (network name + first admin user). */
final readonly class SetupService
{
    private const string NETWORK_NAME_KEY = 'network_name';

    public function __construct(
        private AppSettingsRepository $settings,
    ) {}

    public function needsSetup(): bool
    {
        return $this->userCount() === 0;
    }

    public function networkName(): ?string
    {
        return $this->settings->get(self::NETWORK_NAME_KEY);
    }

    public function defaultLocale(): string
    {
        return $this->settings->defaultLocale();
    }

    public function initialize(
        string $networkName,
        string $adminName,
        ?string $adminEmail,
        string $username,
        string $password,
        string $locale = 'en',
    ): void {
        if (! $this->needsSetup()) {
            throw new RuntimeException('Setup is already completed.');
        }

        $this->seedRoles();

        $defaultLocale = LocaleSupport::normalize($locale);

        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        query('people')->insert([
            'name' => $adminName,
            'email' => $adminEmail,
            'department' => 'Architecture',
            'role_title' => 'Administrator',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $people = query('people')->select()->orderBy('id')->all();
        $personId = (int) end($people)['id'];

        query('users')->insert([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => $defaultLocale,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $userId = (int) query('users')->select()->whereField('username', $username)->first()['id'];
        $adminRoleId = (int) query('roles')->select()->whereField('name', UserRole::ADMIN->value)->first()['id'];

        query('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $adminRoleId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $this->settings->set(self::NETWORK_NAME_KEY, $networkName);
        $this->settings->setDefaultLocale($defaultLocale);
    }

    private function userCount(): int
    {
        return query('users')->count()->execute();
    }

    private function seedRoles(): void
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        foreach (UserRole::cases() as $role) {
            $existing = query('roles')->select()->whereField('name', $role->value)->first();
            if ($existing !== null) {
                continue;
            }

            query('roles')->insert([
                'name' => $role->value,
                'description' => ucfirst($role->value) . ' role',
                'created_at' => $now,
                'updated_at' => $now,
            ])->execute();
        }
    }
}
