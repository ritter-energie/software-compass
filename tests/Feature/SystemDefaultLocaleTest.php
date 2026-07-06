<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Setup\SetupService;
use App\Application\User\AdminUserService;
use App\Infrastructure\Persistence\AppSettingsRepository;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;

final class SystemDefaultLocaleTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();
        $this->clearData();
    }

    protected function tearDown(): void
    {
        $this->clearData();

        parent::tearDown();
    }

    public function test_setup_stores_default_locale_and_applies_it_to_initial_admin(): void
    {
        $settings = new AppSettingsRepository();

        new SetupService($settings)->initialize(
            networkName: 'Architecture Network',
            adminName: 'Admin User',
            adminEmail: 'admin@example.test',
            username: 'admin',
            password: 'secret123',
            locale: 'de',
        );

        self::assertSame('de', $settings->defaultLocale());
        self::assertSame('de', (string) query('users')->select()->whereField('username', 'admin')->first()['preferred_locale']);
    }

    public function test_new_users_fall_back_to_system_default_locale(): void
    {
        $settings = new AppSettingsRepository();
        $settings->setDefaultLocale('de');

        new AdminUserService($settings)->createUser(
            name: 'Viewer User',
            email: 'viewer@example.test',
            username: 'viewer',
            password: 'secret123',
            locale: null,
            role: 'viewer',
        );

        self::assertSame('de', (string) query('users')->select()->whereField('username', 'viewer')->first()['preferred_locale']);
    }

    private function clearData(): void
    {
        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('roles')->delete()->allowAll()->execute();
        query('app_settings')->delete()->allowAll()->execute();
    }
}
