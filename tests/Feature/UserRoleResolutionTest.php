<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infrastructure\Security\CurrentUser;
use Tempest\Http\Session\Session;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class UserRoleResolutionTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();

        query('people')->insert([
            'name' => 'Robin Role',
            'email' => 'robin@example.test',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $personId = (int) query('people')->select()->whereField('email', 'robin@example.test')->first()['id'];

        query('users')->insert([
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'person_id' => $personId,
            'is_active' => true,
            'preferred_locale' => 'en',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        query('roles')->insert([
            'name' => 'admin',
            'description' => 'Admin role',
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
    }

    public function test_authenticated_user_roles_are_loaded_into_current_user_context(): void
    {
        $personId = (int) query('people')->select()->whereField('email', 'robin@example.test')->first()['id'];
        $userId = (int) query('users')->select()->whereField('person_id', $personId)->first()['id'];
        get(Session::class)->set('auth_user_id', $userId);

        $this->http->get('/dashboard')->assertOk();

        $this->assertTrue(CurrentUser::hasRole('admin'));
        $this->assertSame(['admin'], CurrentUser::roles());
    }
}
