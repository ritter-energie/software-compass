<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Component\Component;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ComponentTest extends TestCase
{
    public function test_it_rejects_a_blank_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A component name must not be blank.');

        $this->makeComponent(name: '   ');
    }

    public function test_it_is_incomplete_without_owners_purpose_location_or_environment(): void
    {
        $component = $this->makeComponent(
            businessOwnerId: null,
            technicalOwnerId: null,
            purpose: null,
            deploymentLocationId: null,
            environmentId: null,
        );

        $this->assertTrue($component->isIncomplete());
        $this->assertCount(5, $component->incompletenessReasons());
    }

    public function test_it_is_complete_when_all_governance_fields_are_set(): void
    {
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

    public function test_rename_updates_the_name(): void
    {
        $component = $this->makeComponent(name: 'CRM');
        $component->rename('CRM v2');

        $this->assertSame('CRM v2', $component->name());
    }

    public function test_rename_rejects_a_blank_name(): void
    {
        $component = $this->makeComponent();

        $this->expectException(InvalidArgumentException::class);

        $component->rename('   ');
    }

    private function makeComponent(
        string $name = 'CRM',
        ?int $businessOwnerId = 1,
        ?int $technicalOwnerId = 1,
        ?string $purpose = 'Manages customers.',
        ?int $deploymentLocationId = 1,
        ?int $environmentId = 1,
    ): Component {
        return new Component(
            id: null,
            name: $name,
            shortName: null,
            slug: 'crm',
            componentTypeId: 1,
            statusId: 1,
            criticalityId: null,
            businessOwnerId: $businessOwnerId,
            technicalOwnerId: $technicalOwnerId,
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
        );
    }
}

