<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class ArchitectureInventoryPagesTest extends IntegrationTestCase
{
    private int $personId;
    private int $statusId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();
        $this->seedInventoryData();
        $this->authenticateAs($this->seedUser());
    }

    public function test_dependencies_index_renders_for_authenticated_users(): void
    {
        $this->assertGreaterThan(0, query('dependencies')->count()->execute());

        $this->http->get('/dependencies')->assertOk();
    }

    public function test_journeys_index_renders_for_authenticated_users(): void
    {
        $this->assertGreaterThan(0, query('journeys')->count()->execute());

        $this->http->get('/journeys')->assertOk();
    }

    private function authenticateAs(int $userId): void
    {
        get(Session::class)->set('auth_user_id', $userId);
    }

    private function seedInventoryData(): void
    {
        $this->personId = $this->seedPerson();
        $componentTypeId = $this->seedLookup('component_types', 'Application');
        $this->statusId = $this->seedLookup('component_statuses', 'Active', ['sort_order' => 1]);
        $dependencyTypeId = $this->seedLookup('dependency_types', 'REST API');
        $sourceComponentId = $this->seedComponent('Source App', 'source-app', $componentTypeId);
        $targetComponentId = $this->seedComponent('Target App', 'target-app', $componentTypeId);

        query('dependencies')->insert([
            'source_component_id' => $sourceComponentId,
            'target_component_id' => $targetComponentId,
            'dependency_type_id' => $dependencyTypeId,
            'protocol_id' => null,
            'status_id' => $this->statusId,
            'criticality_id' => null,
            'owner_id' => $this->personId,
            'name' => 'Source to Target API',
            'description' => 'Integration used by the feature test.',
            'data_description' => 'Example Data',
            'frequency' => 'Daily',
            'direction' => 'source_to_target',
            'authentication_method' => null,
            'documentation_url' => null,
            'technical_notes' => null,
            'is_bidirectional' => false,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        query('journeys')->insert([
            'name' => 'Example Journey',
            'slug' => 'example-journey',
            'description' => 'Journey used by the feature test.',
            'owner_id' => $this->personId,
            'status_id' => $this->statusId,
            'sort_order' => 1,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();
    }

    private function seedPerson(): int
    {
        query('people')->insert([
            'name' => 'Feature Owner',
            'email' => 'feature-owner@example.test',
            'is_active' => true,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query('people')->select()->whereField('email', 'feature-owner@example.test')->first()['id'];
    }

    private function seedUser(): int
    {
        query('users')->insert([
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'person_id' => $this->personId,
            'preferred_locale' => 'en',
            'is_active' => true,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query('users')->select()->whereField('person_id', $this->personId)->first()['id'];
    }

    /** @param array<string, mixed> $values */
    private function seedLookup(string $table, string $name, array $values = []): int
    {
        query($table)->insert([
            'name' => $name,
            ...$values,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query($table)->select()->whereField('name', $name)->first()['id'];
    }

    private function seedComponent(string $name, string $slug, int $componentTypeId): int
    {
        query('components')->insert([
            'name' => $name,
            'short_name' => $name,
            'slug' => $slug,
            'component_type_id' => $componentTypeId,
            'status_id' => $this->statusId,
            'criticality_id' => null,
            'business_owner_id' => $this->personId,
            'technical_owner_id' => $this->personId,
            'deployment_location_id' => null,
            'environment_id' => null,
            'project_name' => null,
            'started_on' => null,
            'purpose' => 'Feature test component.',
            'description' => 'Feature test component.',
            'documentation_url' => null,
            'repository_url' => null,
            'vendor' => null,
            'lifecycle_notes' => null,
            'is_external' => false,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query('components')->select()->whereField('slug', $slug)->first()['id'];
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
