<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tempest\Http\Status;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class MasterDataManagementTest extends IntegrationTestCase {
    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();

        query('user_roles')->delete()->allowAll()->execute();
        query('users')->delete()->allowAll()->execute();
        query('people')->delete()->allowAll()->execute();
        query('roles')->delete()->allowAll()->execute();
    }

    public function test_admin_can_open_master_data_create_page(): void {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http->get('/master-data/component-types/create')->assertOk();
    }

    public function test_non_admin_cannot_open_master_data_create_page(): void {
        $this->authenticateAs($this->seedUser('viewer-user', 'viewer'));

        $this->http->get('/master-data/component-types/create')->assertStatus(Status::FORBIDDEN);
    }

    public function test_admin_can_create_update_and_delete_master_data_entries(): void {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/master-data/component-types', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
                'name' => 'Platform Capability',
                'description' => 'Groups reusable platform services.',
            ])
            ->assertRedirect('/master-data');

        $created = query('component_types')->select()->whereField('name', 'Platform Capability')->first();
        $this->assertNotNull($created);

        $this->http
            ->post('/master-data/component-types/' . (int) $created['id'], [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
                'name' => 'Platform Capability Updated',
                'description' => 'Updated description.',
            ])
            ->assertRedirect('/master-data');

        $updated = query('component_types')->select()->whereField('id', $created['id'])->first();
        $this->assertSame('Platform Capability Updated', $updated['name']);
        $this->assertSame('Updated description.', $updated['description']);

        $this->http
            ->post('/master-data/component-types/' . (int) $created['id'] . '/delete', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
            ])
            ->assertRedirect('/master-data');

        $this->assertNull(query('component_types')->select()->whereField('id', $created['id'])->first());
    }

    public function test_admin_can_manage_special_master_data_fields(): void {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/master-data/data-objects', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
                'name' => 'Telemetry Data',
                'description' => 'Operational telemetry.',
                'contains_personal_data' => '1',
                'contains_sensitive_data' => '1',
            ])
            ->assertRedirect('/master-data');

        $dataObject = query('data_objects')->select()->whereField('name', 'Telemetry Data')->first();
        $this->assertNotNull($dataObject);
        $this->assertTrue((bool) $dataObject['contains_personal_data']);
        $this->assertTrue((bool) $dataObject['contains_sensitive_data']);

        $this->http
            ->post('/master-data/deployment-locations', [
                Session::CSRF_TOKEN_KEY => get(Session::class)->token,
                'name' => 'Edge Location',
                'location_type' => 'Edge',
                'description' => 'Near-user runtime location.',
            ])
            ->assertRedirect('/master-data');

        $location = query('deployment_locations')->select()->whereField('name', 'Edge Location')->first();
        $this->assertNotNull($location);
        $this->assertSame('Edge', $location['location_type']);
    }

    public function test_invalid_csrf_token_does_not_create_master_data(): void {
        $this->authenticateAs($this->seedUser('admin-user', 'admin'));

        $this->http
            ->post('/master-data/tags', [
                Session::CSRF_TOKEN_KEY => 'invalid-token',
                'name' => 'Invalid CSRF Tag',
            ])
            ->assertRedirect('/master-data/tags/create');

        $this->assertNull(query('tags')->select()->whereField('name', 'Invalid CSRF Tag')->first());
    }

    private function authenticateAs(int $userId): void {
        get(Session::class)->set('auth_user_id', $userId);
    }

    private function seedUser(string $accountName, string $role): int {
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
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
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
