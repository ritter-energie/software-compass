<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tempest\Http\Session\Session;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\get;

final class ComponentRelationshipUiPersistenceTest extends IntegrationTestCase {
    private int $componentTypeId;
    private int $statusId;

    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();
        $this->componentTypeId = $this->seedLookup('component_types', 'UI Test Application');
        $this->statusId = $this->seedLookup('component_statuses', 'UI Test Active');
        $this->authenticateAs($this->seedUser());
    }

    public function test_ui_update_with_parent_component_writes_selected_parent_as_parent_id(): void {
        $parentId = $this->seedComponent('Selected Parent', 'selected-parent');
        $componentId = $this->seedComponent('Component With Parent', 'component-with-parent');

        $this->postComponentUpdate($componentId, 'Component With Parent', [
            'parent_component_id' => (string) $parentId,
        ])->assertRedirect("/components/{$componentId}");

        $rows = query('component_inheritance')->select()->all();

        $this->assertSame([
            [
                'parent_component_id' => $parentId,
                'child_component_id' => $componentId,
            ],
        ], $this->inheritancePairs($rows));
    }

    public function test_ui_update_with_child_components_writes_current_component_as_parent_id(): void {
        $componentId = $this->seedComponent('Component With Children', 'component-with-children');
        $firstChildId = $this->seedComponent('First Selected Child', 'first-selected-child');
        $secondChildId = $this->seedComponent('Second Selected Child', 'second-selected-child');

        $this->postComponentUpdate($componentId, 'Component With Children', [
            'child_component_ids' => [(string) $firstChildId, (string) $secondChildId],
        ])->assertRedirect("/components/{$componentId}");

        $rows = query('component_inheritance')->select()->orderBy('child_component_id')->all();

        $this->assertSame([
            [
                'parent_component_id' => $componentId,
                'child_component_id' => $firstChildId,
            ],
            [
                'parent_component_id' => $componentId,
                'child_component_id' => $secondChildId,
            ],
        ], $this->inheritancePairs($rows));
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function postComponentUpdate(int $componentId, string $name, array $overrides = []): mixed {
        return $this->http->post('/components/' . $componentId, [
            Session::CSRF_TOKEN_KEY => get(Session::class)->token,
            'name' => $name,
            'component_type_id' => (string) $this->componentTypeId,
            'status_id' => (string) $this->statusId,
            ...$overrides,
        ]);
    }

    private function authenticateAs(int $userId): void {
        get(Session::class)->set('auth_user_id', $userId);
    }

    private function seedUser(): int {
        query('people')->insert([
            'name' => 'Component Relationship Tester',
            'email' => 'component-relationship-tester@example.test',
            'is_active' => true,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        $personId = (int) query('people')->select()->whereField('email', 'component-relationship-tester@example.test')->first()['id'];

        query('users')->insert([
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => 'en',
            'is_active' => true,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query('users')->select()->whereField('person_id', $personId)->first()['id'];
    }

    private function seedLookup(string $table, string $name): int {
        $existing = query($table)->select()->whereField('name', $name)->first();

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        query($table)->insert([
            'name' => $name,
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ])->execute();

        return (int) query($table)->select()->whereField('name', $name)->first()['id'];
    }

    private function seedComponent(string $name, string $slug): int {
        query('components')->insert([
            'name' => $name,
            'short_name' => null,
            'slug' => $slug,
            'component_type_id' => $this->componentTypeId,
            'status_id' => $this->statusId,
            'criticality_id' => null,
            'business_owner_id' => null,
            'business_owner_team_id' => null,
            'technical_owner_id' => null,
            'technical_owner_team_id' => null,
            'deployment_location_id' => null,
            'environment_id' => null,
            'project_name' => null,
            'started_on' => null,
            'purpose' => null,
            'description' => null,
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

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{parent_component_id: int, child_component_id: int}>
     */
    private function inheritancePairs(array $rows): array {
        return array_map(static fn (array $row): array => [
            'parent_component_id' => (int) $row['parent_component_id'],
            'child_component_id' => (int) $row['child_component_id'],
        ], $rows);
    }

    private function now(): string {
        return date('Y-m-d H:i:s');
    }
}


