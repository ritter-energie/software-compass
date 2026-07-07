<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Component\Component;
use App\Infrastructure\Persistence\MariaDbComponentRepository;
use DateTimeImmutable;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;

final class ComponentInheritancePersistenceTest extends IntegrationTestCase {
    private MariaDbComponentRepository $components;
    private int $componentTypeId;
    private int $statusId;

    protected function setUp(): void {
        parent::setUp();

        $this->database->setup();
        $this->components = new MariaDbComponentRepository();
        $this->componentTypeId = $this->lookupId('component_types', 'Application');
        $this->statusId = $this->lookupId('component_statuses', 'Active');
    }

    public function test_it_persists_single_parent_and_multiple_child_components(): void {
        $parentA = $this->components->save($this->component('Parent A', 'parent-a'));
        $childA = $this->components->save($this->component('Child A', 'child-a'));
        $childB = $this->components->save($this->component('Child B', 'child-b'));

        $center = $this->components->save($this->component(
            name: 'Center',
            slug: 'center',
            parentComponentId: (int) $parentA->id(),
            childComponentIds: [(int) $childA->id(), (int) $childB->id()],
        ));

        $stored = $this->components->findById((int) $center->id());

        $this->assertNotNull($stored);
        $this->assertSame((int) $parentA->id(), $stored->parentComponentId());
        $this->assertSame([(int) $childA->id(), (int) $childB->id()], $stored->childComponentIds());
        $this->assertSame(['Parent A'], array_map(static fn (Component $component): string => $component->name(), $this->components->parentsOf((int) $center->id())));
        $this->assertSame(['Child A', 'Child B'], array_map(static fn (Component $component): string => $component->name(), $this->components->childrenOf((int) $center->id())));
    }

    public function test_it_replaces_parent_and_child_assignments_on_update(): void {
        $parentA = $this->components->save($this->component('Replace Parent A', 'replace-parent-a'));
        $parentB = $this->components->save($this->component('Replace Parent B', 'replace-parent-b'));
        $childA = $this->components->save($this->component('Replace Child A', 'replace-child-a'));
        $childB = $this->components->save($this->component('Replace Child B', 'replace-child-b'));

        $center = $this->components->save($this->component(
            name: 'Replace Center',
            slug: 'replace-center',
            parentComponentId: (int) $parentA->id(),
            childComponentIds: [(int) $childA->id()],
        ));

        $updated = $this->components->save($this->component(
            id: $center->id(),
            name: 'Replace Center',
            slug: 'replace-center',
            parentComponentId: (int) $parentB->id(),
            childComponentIds: [(int) $childB->id()],
        ));

        $this->assertSame((int) $parentB->id(), $updated->parentComponentId());
        $this->assertSame([(int) $childB->id()], $updated->childComponentIds());
        $this->assertSame(2, count(query('component_inheritance')->select()->all()));
    }

    public function test_it_reassigns_children_when_a_new_parent_claims_them(): void {
        $parentA = $this->components->save($this->component('Parent A Claim', 'parent-a-claim'));
        $parentB = $this->components->save($this->component('Parent B Claim', 'parent-b-claim'));
        $child = $this->components->save($this->component(
            name: 'Child Claim',
            slug: 'child-claim',
            parentComponentId: (int) $parentA->id(),
        ));

        $this->components->save($this->component(
            id: $parentB->id(),
            name: 'Parent B Claim',
            slug: 'parent-b-claim',
            childComponentIds: [(int) $child->id()],
        ));

        $updatedChild = $this->components->findById((int) $child->id());

        $this->assertNotNull($updatedChild);
        $this->assertSame((int) $parentB->id(), $updatedChild->parentComponentId());
        $this->assertSame(1, count(query('component_inheritance')->select()->all()));
    }

    private function component(
        string $name,
        string $slug,
        ?int $id = null,
        ?int $parentComponentId = null,
        array $childComponentIds = [],
    ): Component {
        return new Component(
            id: $id,
            name: $name,
            shortName: null,
            slug: $slug,
            componentTypeId: $this->componentTypeId,
            statusId: $this->statusId,
            criticalityId: null,
            businessOwnerId: null,
            businessOwnerTeamId: null,
            technicalOwnerId: null,
            technicalOwnerTeamId: null,
            deploymentLocationId: null,
            environmentId: null,
            projectName: null,
            startedOn: null,
            purpose: null,
            description: null,
            documentationUrl: null,
            repositoryUrl: null,
            vendor: null,
            lifecycleNotes: null,
            isExternal: false,
            parentComponentId: $parentComponentId,
            childComponentIds: $childComponentIds,
        );
    }

    private function lookupId(string $table, string $name): int {
        $existing = query($table)->select()->whereField('name', $name)->first();

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        query($table)->insert([
            'name' => $name,
            'created_at' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
            'updated_at' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
        ])->execute();

        return (int) query($table)->select()->whereField('name', $name)->first()['id'];
    }
}
