<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

/**
 * Verifies session-based login: unauthenticated requests are redirected to the
 * login form, and valid credentials create an authenticated session.
 *
 * @internal This resets and re-migrates the configured database; do not
 *           run against a database containing data you want to keep.
 */
final class BasicAuthTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();
        get(Session::class)->remove('auth_user_id');

        query('people')->insert([
            'name' => 'Ada Example',
            'email' => 'ada@example.test',
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();

        $personId = (int) query('people')->select()->whereField('email', 'ada@example.test')->first()['id'];

        query('users')->insert([
            'username' => 'ada',
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'person_id' => $personId,
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ])->execute();
    }

    public function test_it_redirects_unauthenticated_requests_to_login(): void
    {
        $this->http
            ->get('/dashboard')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', '/login');
    }

    public function test_it_redirects_to_login_when_session_user_is_unknown(): void
    {
        get(Session::class)->set('auth_user_id', 999999);

        $this->http
            ->get('/dashboard')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', '/login');
    }

    public function test_login_form_is_accessible_with_stale_session_user_id(): void
    {
        get(Session::class)->set('auth_user_id', 999999);

        $this->http->get('/login')->assertOk();
    }

    public function test_it_accepts_requests_with_valid_session_user(): void
    {
        $userId = (int) query('users')->select()->whereField('username', 'ada')->first()['id'];
        get(Session::class)->set('auth_user_id', $userId);

        $this->http->get('/dashboard')->assertOk();
    }
}
