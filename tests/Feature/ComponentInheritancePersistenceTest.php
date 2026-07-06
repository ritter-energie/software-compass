<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Component\Component;
use App\Infrastructure\Persistence\MariaDbComponentRepository;
use DateTimeImmutable;
use Tests\IntegrationTestCase;

use function Tempest\Database\query;

final class ComponentInheritancePersistenceTest extends IntegrationTestCase
{
    private MariaDbComponentRepository $components;
    private int $componentTypeId;
    private int $statusId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->database->setup();
        $this->components = new MariaDbComponentRepository();
        $this->componentTypeId = $this->lookupId('component_types', 'Application');
        $this->statusId = $this->lookupId('component_statuses', 'Active');
    }

    public function test_it_persists_multiple_parent_and_child_components(): void
    {
        $parentA = $this->components->save($this->component('Parent A', 'parent-a'));
        $parentB = $this->components->save($this->component('Parent B', 'parent-b'));
        $childA = $this->components->save($this->component('Child A', 'child-a'));
        $childB = $this->components->save($this->component('Child B', 'child-b'));

        $center = $this->components->save($this->component(
            name: 'Center',
            slug: 'center',
            parentComponentIds: [(int) $parentA->id(), (int) $parentB->id()],
            childComponentIds: [(int) $childA->id(), (int) $childB->id()],
        ));

        $stored = $this->components->findById((int) $center->id());

        $this->assertNotNull($stored);
        $this->assertSame([(int) $parentA->id(), (int) $parentB->id()], $stored->parentComponentIds());
        $this->assertSame([(int) $childA->id(), (int) $childB->id()], $stored->childComponentIds());
        $this->assertSame(['Parent A', 'Parent B'], array_map(static fn (Component $component): string => $component->name(), $this->components->parentsOf((int) $center->id())));
        $this->assertSame(['Child A', 'Child B'], array_map(static fn (Component $component): string => $component->name(), $this->components->childrenOf((int) $center->id())));
    }

    public function test_it_replaces_parent_and_child_assignments_on_update(): void
    {
        $parentA = $this->components->save($this->component('Replace Parent A', 'replace-parent-a'));
        $parentB = $this->components->save($this->component('Replace Parent B', 'replace-parent-b'));
        $childA = $this->components->save($this->component('Replace Child A', 'replace-child-a'));
        $childB = $this->components->save($this->component('Replace Child B', 'replace-child-b'));

        $center = $this->components->save($this->component(
            name: 'Replace Center',
            slug: 'replace-center',
            parentComponentIds: [(int) $parentA->id()],
            childComponentIds: [(int) $childA->id()],
        ));

        $updated = $this->components->save($this->component(
            id: $center->id(),
            name: 'Replace Center',
            slug: 'replace-center',
            parentComponentIds: [(int) $parentB->id()],
            childComponentIds: [(int) $childB->id()],
        ));

        $this->assertSame([(int) $parentB->id()], $updated->parentComponentIds());
        $this->assertSame([(int) $childB->id()], $updated->childComponentIds());
        $this->assertSame(2, count(query('component_inheritance')->select()->all()));
    }

    private function component(
        string $name,
        string $slug,
        ?int $id = null,
        array $parentComponentIds = [],
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
            technicalOwnerId: null,
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
            parentComponentIds: $parentComponentIds,
            childComponentIds: $childComponentIds,
        );
    }

    private function lookupId(string $table, string $name): int
    {
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
