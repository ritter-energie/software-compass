<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class AccountPageTest extends IntegrationTestCase {
    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('roles')->delete()->allowAll()->execute();

        query('roles')->insert([
            'name' => 'admin',
            'description' => 'Admin role',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    public function test_account_page_requires_authentication(): void {
        $this->seedUser('existing-user@example.test', 'secret');

        $this->http
            ->get('/account')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', '/login');
    }

    public function test_authenticated_user_can_open_account_page(): void {
        $userId = $this->seedUser('account-user@example.test', 'secret');
        get(Session::class)->set('auth_user_id', $userId);

        $this->http
            ->get('/account')
            ->assertOk()
            ->assertSee('account-user@example.test');
    }

    public function test_authenticated_header_shows_initials_account_menu(): void {
        $userId = $this->seedUser('account-menu-user@example.test', 'secret');
        get(Session::class)->set('auth_user_id', $userId);

        $this->http
            ->get('/account')
            ->assertOk()
            ->assertSee('data-user-menu')
            ->assertSee('AU')
            ->assertSee('Account menu')
            ->assertSee('Sign out');
    }

    private function seedUser(string $email, string $password): int {
        query('people')->insert([
            'name' => 'Account User',
            'email' => $email,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $personId = (int) query('people')->select()->whereField('email', $email)->first()['id'];

        query('users')->insert([
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => 'en',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $userId = (int) query('users')->select()->whereField('person_id', $personId)->first()['id'];
        $roleId = (int) query('roles')->select()->whereField('name', 'admin')->first()['id'];

        query('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        return $userId;
    }
}
