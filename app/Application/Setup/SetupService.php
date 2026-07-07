<?php

declare(strict_types=1);

namespace App\Application\Setup;

use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Shared\Enum\UserRole;
use App\Shared\Support\LocaleSupport;
use DateTimeImmutable;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Migrations\MigrationManager;
use Tempest\Database\Query;

use function Tempest\Database\query;

/** Handles one-time first-run initialization (network name + first admin user). */
final readonly class SetupService {
    private const string NETWORK_NAME_KEY = 'network_name';

    public function __construct(
        private AppSettingsRepository $settings,
        private MigrationManager $migrationManager,
        private Database $database,
    ) {}

    public function needsSetup(): bool {
        return $this->userCount() === 0;
    }

    public function networkName(): ?string {
        return $this->settings->get(self::NETWORK_NAME_KEY);
    }

    public function defaultLocale(): string {
        return $this->settings->defaultLocale();
    }

    public function initialize(
        string $networkName,
        string $adminName,
        string $adminEmail,
        string $password,
        string $locale = 'en',
    ): void {
        if (! $this->needsSetup()) {
            throw new RuntimeException('Setup is already completed.');
        }

        $this->seedRoles();

        $defaultLocale = LocaleSupport::normalize($locale);
        $adminEmail = $this->normalizeEmail($adminEmail);

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
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => $defaultLocale,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        $userId = (int) query('users')->select()->whereField('person_id', $personId)->first()['id'];
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

    private function userCount(): int {
        try {
            return query('users')->count()->execute();
        } catch (QueryWasInvalid $exception) {
            if (! $this->isMissingUsersTable($exception)) {
                throw $exception;
            }

            if (! $this->databaseHasNoTables()) {
                throw new RuntimeException(
                    'Database schema is inconsistent: table `users` is missing while the database is not empty.',
                    previous: $exception,
                );
            }

            $this->migrationManager->up();

            return query('users')->count()->execute();
        }
    }

    private function isMissingUsersTable(QueryWasInvalid $exception): bool {
        $previous = $exception->getPrevious();
        if ($previous instanceof PDOException && (string) $previous->getCode() === '42S02') {
            return true;
        }

        $message = strtolower($exception->getMessage());

        return str_contains($message, 'users') && (str_contains($message, "doesn't exist") || str_contains($message, 'base table or view not found'));
    }

    private function databaseHasNoTables(): bool {
        $result = new Query(
            'SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema = DATABASE()',
        )
            ->onDatabase($this->database->tag)
            ->fetchFirst();

        return (int) ($result['table_count'] ?? 0) === 0;
    }

    private function normalizeEmail(string $email): string {
        $normalized = strtolower(trim($email));
        if (filter_var($normalized, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Please enter a valid email address.');
        }

        return $normalized;
    }

    private function seedRoles(): void {
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
