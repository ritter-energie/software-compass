<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Status;

use function Tempest\Database\query;

use Tests\IntegrationTestCase;

final class SetupFlowTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('app_settings')->delete()->allowAll()->execute();
    }

    public function test_dashboard_redirects_to_setup_when_no_users_exist(): void
    {
        $this->http->get('/dashboard')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', '/setup');
    }

    public function test_setup_page_is_public_when_no_users_exist(): void
    {
        $this->http->get('/setup')->assertOk();
    }
}

