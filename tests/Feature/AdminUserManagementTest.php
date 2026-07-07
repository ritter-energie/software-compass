<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class AdminUserManagementTest extends IntegrationTestCase {
    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('roles')->delete()->allowAll()->execute();

        $this->seedRole('admin');
        $this->seedRole('viewer');
    }

    public function test_admin_can_access_user_management(): void {
        $adminId = $this->seedUser('admin-user@example.test', 'secret', 'admin');
        $this->authenticateAs($adminId);

        $this->http->get('/admin/users')->assertOk();
    }

    public function test_non_admin_gets_forbidden_on_user_management(): void {
        $viewerId = $this->seedUser('viewer-user@example.test', 'secret', 'viewer');
        $this->authenticateAs($viewerId);

        $this->http->get('/admin/users')->assertStatus(Status::FORBIDDEN);
    }

    public function test_admin_can_open_user_edit_page(): void {
        $adminId = $this->seedUser('admin-user@example.test', 'secret', 'admin');
        $editedUserId = $this->seedUser('editor-user@example.test', 'secret', 'viewer');
        $this->authenticateAs($adminId);

        $this->http->get("/admin/users/{$editedUserId}/edit")->assertOk();
    }

    public function test_non_admin_gets_forbidden_on_user_edit_page(): void {
        $viewerId = $this->seedUser('viewer-user@example.test', 'secret', 'viewer');
        $editedUserId = $this->seedUser('editor-user@example.test', 'secret', 'viewer');
        $this->authenticateAs($viewerId);

        $this->http->get("/admin/users/{$editedUserId}/edit")->assertStatus(Status::FORBIDDEN);
    }

    private function authenticateAs(int $userId): void {
        get(Session::class)->set('auth_user_id', $userId);
    }

    private function seedRole(string $name): int {
        query('roles')->insert([
            'name' => $name,
            'description' => ucfirst($name) . ' role',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        return (int) query('roles')->select()->whereField('name', $name)->first()['id'];
    }

    private function seedUser(string $email, string $password, string $role): int {
        query('people')->insert([
            'name' => ucfirst(strstr($email, '@', true) ?: $email),
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
        $roleId = (int) query('roles')->select()->whereField('name', $role)->first()['id'];

        query('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        return $userId;
    }
}
