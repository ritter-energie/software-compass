<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Database\Migrations\Migration;
use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class AdminDatabaseUpdateTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('roles')->delete()->allowAll()->execute();
        query('app_settings')->delete()->allowAll()->execute();
    }

    public function test_admin_can_open_app_settings_page(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('App Settings')
            ->assertSee('Database Update');
    }

    public function test_non_admin_cannot_open_app_settings_page(): void
    {
        $this->authenticateAs($this->seedUser('viewer-user', 'viewer'));

        $this->http->get('/admin/settings')->assertStatus(Status::FORBIDDEN);
    }

    public function test_admin_sees_app_settings_link_in_account_menu(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('href="/admin/settings"')
            ->assertSee('App Settings');
    }

    public function test_admin_sees_pending_migration_when_migration_history_is_missing_entry(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $executedMigrations = Migration::select()->all();
        $lastMigration = $executedMigrations[array_key_last($executedMigrations)];
        $lastMigration->delete();

        $this->http
            ->get('/admin/settings')
            ->assertOk()
            ->assertSee('Pending migrations')
            ->assertSee($lastMigration->name);
    }

    public function test_admin_can_submit_update_when_no_migrations_are_pending(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/admin/settings/database-update', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
            ])
            ->assertRedirect('/admin/settings');
    }

    public function test_invalid_csrf_token_does_not_start_database_update(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/admin/settings/database-update', [
                Session::CSRF_TOKEN_KEY => 'invalid-token',
            ])
            ->assertRedirect('/admin/settings');
    }

    public function test_admin_can_update_default_locale_from_app_settings(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/admin/settings/default-locale', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
                'default_locale' => 'de',
            ])
            ->assertRedirect('/admin/settings');

        self::assertSame('de', (string) query('app_settings')->select()->whereField('setting_key', 'default_locale')->first()['setting_value']);
    }

    public function test_legacy_database_update_page_redirects_to_app_settings(): void
    {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http->get('/admin/database')->assertRedirect('/admin/settings');
    }

    private function authenticateAs(int $userId): void
    {
        get(Session::class)->set('auth_user_id', $userId);
    }

    private function seedUser(string $accountName, string $role): int
    {
        $email = $accountName . '@example.test';

        query('people')->insert([
            'name' => ucfirst($accountName),
            'email' => $email,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        query('roles')->insert([
            'name' => $role,
            'description' => ucfirst($role) . ' role',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $personId = (int) query('people')->select()->whereField('email', $email)->first()['id'];
        $roleId = (int) query('roles')->select()->whereField('name', $role)->first()['id'];

        query('users')->insert([
            'password_hash' => password_hash('secret123', PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => 'en',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $userId = (int) query('users')->select()->whereField('person_id', $personId)->first()['id'];

        query('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        return $userId;
    }
}
