<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Shared\Enum\UserRole;
use App\Shared\Support\LocaleSupport;
use DateTimeImmutable;
use InvalidArgumentException;

use function Tempest\Database\query;

/** Admin-facing user provisioning and read-model helpers. */
final readonly class AdminUserService
{
    public function __construct(
        private AppSettingsRepository $settings,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function users(): array
    {
        $users = query('users')->select()->orderBy('username')->all();
        if ($users === []) {
            return [];
        }

        $personIds = array_values(array_unique(array_filter(array_map(static fn (array $user): ?int => $user['person_id'] !== null ? (int) $user['person_id'] : null, $users))));
        $people = $personIds === []
            ? []
            : query('people')->select()->whereIn('id', $personIds)->all();
        $peopleById = [];
        foreach ($people as $person) {
            $peopleById[(int) $person['id']] = $person;
        }

        $userIds = array_map(static fn (array $user): int => (int) $user['id'], $users);
        $assignments = query('user_roles')->select()->whereIn('user_id', $userIds)->all();
        $roleIds = array_values(array_unique(array_map(static fn (array $assignment): int => (int) $assignment['role_id'], $assignments)));
        $rolesById = [];
        if ($roleIds !== []) {
            foreach (query('roles')->select()->whereIn('id', $roleIds)->all() as $role) {
                $rolesById[(int) $role['id']] = (string) $role['name'];
            }
        }

        $rolesByUser = [];
        foreach ($assignments as $assignment) {
            $userId = (int) $assignment['user_id'];
            $roleName = $rolesById[(int) $assignment['role_id']] ?? null;
            if ($roleName === null) {
                continue;
            }
            $rolesByUser[$userId] ??= [];
            $rolesByUser[$userId][] = $roleName;
        }

        return array_map(function (array $user) use ($peopleById, $rolesByUser): array {
            $person = $user['person_id'] !== null ? $peopleById[(int) $user['person_id']] ?? null : null;

            return [
                'id' => (int) $user['id'],
                'username' => (string) $user['username'],
                'is_active' => (bool) $user['is_active'],
                'preferred_locale' => (string) ($user['preferred_locale'] ?? 'en'),
                'person_name' => $person['name'] ?? null,
                'person_email' => $person['email'] ?? null,
                'roles' => $rolesByUser[(int) $user['id']] ?? [],
            ];
        }, $users);
    }

    /** @return string[] */
    public function availableRoles(): array
    {
        return UserRole::values();
    }

    public function defaultLocale(): string
    {
        return $this->settings->defaultLocale();
    }

    /** @return array<string, mixed>|null */
    public function userById(int $id): ?array
    {
        foreach ($this->users() as $user) {
            if ((int) $user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    public function createUser(
        string $name,
        ?string $email,
        string $username,
        string $password,
        ?string $locale,
        string $role,
    ): void {
        $this->seedRoles();

        if (! in_array($role, UserRole::values(), true)) {
            throw new InvalidArgumentException('Invalid role selected.');
        }

        if (query('users')->select()->whereField('username', $username)->first() !== null) {
            throw new InvalidArgumentException('Username already exists.');
        }

        $preferredLocale = LocaleSupport::normalize($locale, $this->defaultLocale());
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        query('people')->insert([
            'name' => $name,
            'email' => $email,
            'department' => null,
            'role_title' => null,
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
            'preferred_locale' => $preferredLocale,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        /** @var array<string, mixed>|null $createdUser */
        $createdUser = query('users')->select()->whereField('username', $username)->first();
        /** @var array<string, mixed>|null $selectedRole */
        $selectedRole = query('roles')->select()->whereField('name', $role)->first();
        if ($createdUser === null || $selectedRole === null) {
            throw new InvalidArgumentException('Unable to create user with selected role.');
        }

        $userId = (int) $createdUser['id'];
        $roleId = (int) $selectedRole['id'];

        query('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();
    }

    public function updateUser(
        int $id,
        string $name,
        ?string $email,
        string $username,
        ?string $password,
        string $locale,
        string $role,
    ): void {
        $this->seedRoles();

        if (! in_array($role, UserRole::values(), true)) {
            throw new InvalidArgumentException('Invalid role selected.');
        }

        $user = query('users')->select()->whereField('id', $id)->first();
        if ($user === null) {
            throw new InvalidArgumentException('User not found.');
        }

        $usernameOwner = query('users')->select()->whereField('username', $username)->first();
        if ($usernameOwner !== null && (int) $usernameOwner['id'] !== $id) {
            throw new InvalidArgumentException('Username already exists.');
        }

        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $personId = $user['person_id'] !== null ? (int) $user['person_id'] : null;

        if ($personId !== null) {
            query('people')
                ->update(
                    name: $name,
                    email: $email,
                    updated_at: $now,
                )
                ->whereField('id', $personId)
                ->execute();
        }

        $userUpdate = [
            'username' => $username,
            'preferred_locale' => LocaleSupport::normalize($locale, $this->defaultLocale()),
            'updated_at' => $now,
        ];

        if ($password !== null && $password !== '') {
            $userUpdate['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        query('users')->update(...$userUpdate)->whereField('id', $id)->execute();

        $selectedRole = query('roles')->select()->whereField('name', $role)->first();
        if ($selectedRole === null) {
            throw new InvalidArgumentException('Invalid role selected.');
        }

        $roleId = (int) $selectedRole['id'];
        query('user_roles')->delete()->whereField('user_id', $id)->execute();
        query('user_roles')->insert([
            'user_id' => $id,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();
    }

    private function seedRoles(): void
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        foreach (UserRole::cases() as $role) {
            if (query('roles')->select()->whereField('name', $role->value)->first() !== null) {
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

    public function toggleActive(int $userId): void
    {
        $user = query('users')->select()->whereField('id', $userId)->first();
        if ($user === null) {
            return;
        }

        query('users')
            ->update(is_active: ! (bool) $user['is_active'], updated_at: new DateTimeImmutable()->format('Y-m-d H:i:s'))
            ->whereField('id', $userId)
            ->execute();
    }
}
