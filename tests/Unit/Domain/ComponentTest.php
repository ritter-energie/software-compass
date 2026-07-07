<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Component\Component;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ComponentTest extends TestCase {
    public function test_it_rejects_a_blank_name(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A component name must not be blank.');

        $this->makeComponent(name: '   ');
    }

    public function test_it_is_incomplete_without_owners_purpose_location_or_environment(): void {
        $component = $this->makeComponent(
            businessOwnerId: null,
            businessOwnerTeamId: null,
            technicalOwnerId: null,
            purpose: null,
            deploymentLocationId: null,
            environmentId: null,
        );

        $this->assertTrue($component->isIncomplete());
        $this->assertCount(5, $component->incompletenessReasons());
    }

    public function test_it_is_complete_when_all_governance_fields_are_set(): void {
        $component = $this->makeComponent(
            businessOwnerId: 1,
            technicalOwnerId: 2,
            purpose: 'Manages customers.',
            deploymentLocationId: 1,
            environmentId: 1,
        );

        $this->assertFalse($component->isIncomplete());
        $this->assertSame([], $component->incompletenessReasons());
    }

    public function test_team_owners_satisfy_governance_ownership(): void {
        $component = $this->makeComponent(
            businessOwnerId: null,
            businessOwnerTeamId: 10,
            technicalOwnerId: null,
            technicalOwnerTeamId: 11,
        );

        $this->assertFalse($component->isIncomplete());
        $this->assertSame([], $component->incompletenessReasons());
    }

    public function test_rename_updates_the_name(): void {
        $component = $this->makeComponent(name: 'CRM');
        $component->rename('CRM v2');

        $this->assertSame('CRM v2', $component->name());
    }

    public function test_rename_rejects_a_blank_name(): void {
        $component = $this->makeComponent();

        $this->expectException(InvalidArgumentException::class);

        $component->rename('   ');
    }

    public function test_it_can_have_multiple_parent_and_child_components(): void {
        $component = $this->makeComponent(
            parentComponentIds: [2, 3, 2],
            childComponentIds: [4, 5, 4],
        );

        $this->assertSame([2, 3], $component->parentComponentIds());
        $this->assertSame([4, 5], $component->childComponentIds());
    }

    public function test_it_rejects_self_references(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A component cannot inherit from itself.');

        $this->makeComponent(id: 1, parentComponentIds: [1]);
    }

    public function test_it_rejects_the_same_component_as_parent_and_child(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A component cannot inherit from and be parent of the same component.');

        $this->makeComponent(parentComponentIds: [2], childComponentIds: [2]);
    }

    private function makeComponent(
        ?int $id = null,
        string $name = 'CRM',
        ?int $businessOwnerId = 1,
        ?int $businessOwnerTeamId = null,
        ?int $technicalOwnerId = 1,
        ?int $technicalOwnerTeamId = null,
        ?string $purpose = 'Manages customers.',
        ?int $deploymentLocationId = 1,
        ?int $environmentId = 1,
        array $parentComponentIds = [],
        array $childComponentIds = [],
    ): Component {
        return new Component(
            id: $id,
            name: $name,
            shortName: null,
            slug: 'crm',
            componentTypeId: 1,
            statusId: 1,
            criticalityId: null,
            businessOwnerId: $businessOwnerId,
            businessOwnerTeamId: $businessOwnerTeamId,
            technicalOwnerId: $technicalOwnerId,
            technicalOwnerTeamId: $technicalOwnerTeamId,
            deploymentLocationId: $deploymentLocationId,
            environmentId: $environmentId,
            projectName: null,
            startedOn: null,
            purpose: $purpose,
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
}
