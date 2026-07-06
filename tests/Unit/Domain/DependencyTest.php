<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Dependency\Dependency;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DependencyTest extends TestCase
{
    public function test_it_rejects_a_dependency_that_targets_its_own_source(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A component cannot depend on itself.');

        $this->makeDependency(sourceComponentId: 1, targetComponentId: 1);
    }

    public function test_it_rejects_a_blank_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A dependency name must not be blank.');

        $this->makeDependency(name: '   ');
    }

    public function test_label_combines_name_and_data_description(): void
    {
        $dependency = $this->makeDependency(name: 'Orders', dataDescription: 'Order Data');

        $this->assertSame('Orders / Order Data', $dependency->label());
    }

    public function test_label_falls_back_to_name_without_data_description(): void
    {
        $dependency = $this->makeDependency(name: 'Orders', dataDescription: null);

        $this->assertSame('Orders', $dependency->label());
    }

    public function test_it_is_incomplete_without_owner_or_data_description(): void
    {
        $dependency = $this->makeDependency(ownerId: null, dataDescription: null);

        $this->assertTrue($dependency->isIncomplete());
    }

    public function test_it_is_complete_with_owner_and_data_description(): void
    {
        $dependency = $this->makeDependency(ownerId: 5, dataDescription: 'Order Data');

        $this->assertFalse($dependency->isIncomplete());
    }

    private function makeDependency(
        int $sourceComponentId = 1,
        int $targetComponentId = 2,
        string $name = 'Orders',
        ?string $dataDescription = 'Order Data',
        ?int $ownerId = 1,
    ): Dependency {
        return new Dependency(
            id: null,
            sourceComponentId: $sourceComponentId,
            targetComponentId: $targetComponentId,
            dependencyTypeId: 1,
            protocolId: null,
            statusId: 1,
            criticalityId: null,
            ownerId: $ownerId,
            name: $name,
            description: null,
            dataDescription: $dataDescription,
            frequency: null,
            direction: Dependency::DIRECTION_SOURCE_TO_TARGET,
            authenticationMethod: null,
            documentationUrl: null,
            technicalNotes: null,
            isBidirectional: false,
        );
    }
}

