<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Setup\SetupService;
use RuntimeException;
use Tempest\Database\Database;
use Tempest\Database\Query;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class SetupFlowTest extends IntegrationTestCase {
    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('app_settings')->delete()->allowAll()->execute();
    }

    public function test_dashboard_redirects_to_setup_when_no_users_exist(): void {
        $this->http
            ->get('/dashboard')
            ->assertStatus(Status::FOUND)
            ->assertHeaderContains('Location', '/setup');
    }

    public function test_setup_page_is_public_when_no_users_exist(): void {
        $this->http->get('/setup')->assertOk();
    }

    public function test_setup_page_bootstraps_schema_when_database_is_empty(): void {
        $this->database->setup(migrate: false);

        $this->http->get('/setup')->assertOk();

        self::assertSame(0, query('users')->count()->execute());
    }

    public function test_setup_does_not_auto_bootstrap_when_users_table_is_missing_but_database_not_empty(): void {
        $this->database->setup(migrate: false);
        get(Database::class)->execute(new Query('CREATE TABLE bootstrap_probe (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY)'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('table `users` is missing while the database is not empty');

        get(SetupService::class)->needsSetup();
    }
}
