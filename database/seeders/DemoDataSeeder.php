<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Shared\Enum\UserRole;
use DateTimeImmutable;
use Tempest\Database\DatabaseSeeder;
use UnitEnum;

use function Tempest\Database\query;

/**
 * Seeds neutral demo data for local development and open-source contributors.
 * No company-specific data or names are included here.
 */
final class DemoDataSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');

        $businessOwner = $this->person('Alex Business', 'alex.business@example.org', 'Sales', 'Business Owner');
        $technicalOwner = $this->person('Taylor Tech', 'taylor.tech@example.org', 'IT', 'Technical Owner');
        $architect = $this->person('Sam Architect', 'sam.architect@example.org', 'Architecture', 'Enterprise Architect');

        $this->seedRoles();
        $adminUserId = $this->user('admin', $architect);
        $this->assignRole($adminUserId, UserRole::ADMIN->value);

        $typeApplication = $this->idByName('component_types', 'Application');
        $typeWebsite = $this->idByName('component_types', 'Website');
        $typeDatabase = $this->idByName('component_types', 'Database');
        $statusActive = $this->idByName('component_statuses', 'Active');
        $criticalityHigh = $this->idByName('criticality_levels', 'High');
        $criticalityBusiness = $this->idByName('criticality_levels', 'Business Critical');
        $envProduction = $this->idByName('environments', 'Production');
        $deployment = $this->idByName('deployment_locations', 'On-Premise Data Center');

        $components = [];
        foreach ([
            ['CRM', 'crm', $typeApplication, $criticalityHigh, 'Manages customer relationships and customer master data.'],
            ['ERP', 'erp', $typeApplication, $criticalityBusiness, 'Core enterprise resource planning system.'],
            ['Webshop', 'webshop', $typeWebsite, $criticalityBusiness, 'Customer-facing online shop.'],
            ['PIM', 'pim', $typeApplication, $criticalityHigh, 'Product information management.'],
            ['Data Warehouse', 'data-warehouse', $typeDatabase, $criticalityHigh, 'Central analytical data store.'],
            ['Finance System', 'finance-system', $typeApplication, $criticalityHigh, 'Accounting and invoice processing.'],
            ['WMS', 'wms', $typeApplication, $criticalityHigh, 'Warehouse management.'],
            ['Website', 'website', $typeWebsite, $criticalityHigh, 'Public corporate website.'],
            ['Manual Excel Import', 'manual-excel-import', $typeApplication, null, 'Manual import workaround for legacy processes.'],
        ] as [$name, $slug, $typeId, $criticalityId, $purpose]) {
            $components[$name] = $this->component($name, $slug, $typeId, $statusActive, $criticalityId, $businessOwner, $technicalOwner, $deployment, $envProduction, $purpose);
        }

        $restApi = $this->idByName('dependency_types', 'REST API');
        $csvExport = $this->idByName('dependency_types', 'CSV Export');
        $etl = $this->idByName('dependency_types', 'ETL');
        $dbExport = $this->idByName('dependency_types', 'Database Access');
        $protocolRest = $this->idByName('communication_protocols', 'REST');
        $protocolSql = $this->idByName('communication_protocols', 'SQL');

        $this->dependency($components['Webshop'], $components['ERP'], 'Orders / REST API', $restApi, $protocolRest, $statusActive, $criticalityBusiness, $technicalOwner, 'Order Data');
        $this->dependency($components['PIM'], $components['Webshop'], 'Product Data / CSV Export', $csvExport, null, $statusActive, $criticalityHigh, $technicalOwner, 'Product Data');
        $this->dependency($components['Webshop'], $components['CRM'], 'Customer Data / REST API', $restApi, $protocolRest, $statusActive, $criticalityHigh, $technicalOwner, 'Customer Data');
        $this->dependency($components['ERP'], $components['Data Warehouse'], 'Revenue Data / ETL', $etl, null, $statusActive, $criticalityHigh, $technicalOwner, 'Analytics Data');
        $this->dependency($components['CRM'], $components['Data Warehouse'], 'Customer Data / ETL', $etl, null, $statusActive, $criticalityHigh, $technicalOwner, 'Customer Data');
        $this->dependency($components['ERP'], $components['Finance System'], 'Invoice Data / Database Export', $dbExport, $protocolSql, $statusActive, $criticalityBusiness, $technicalOwner, 'Invoice Data');
        $this->dependency($components['WMS'], $components['ERP'], 'Delivery Status / API', $restApi, $protocolRest, $statusActive, $criticalityHigh, $technicalOwner, 'Order Data');

        $journeyId = $this->journey('Order to Delivery', 'order-to-delivery', $businessOwner, $statusActive);
        foreach ([
            [1, 'Customer orders in the webshop', $components['Webshop']],
            [2, 'Order is created in ERP', $components['ERP']],
            [3, 'Warehouse receives picking order', $components['WMS']],
            [4, 'Delivery is confirmed', $components['ERP']],
            [5, 'Invoice is created', $components['Finance System']],
        ] as [$sort, $stepName, $componentId]) {
            $stepId = $this->journeyStep($journeyId, $stepName, $sort);
            $this->journeyStepComponent($stepId, $componentId, 'supporting');
        }
    }

    private function idByName(string $table, string $name): int
    {
        return (int) query($table)->select()->whereField('name', $name)->first()['id'];
    }

    private function person(string $name, string $email, string $department, string $roleTitle): int
    {
        return $this->upsert('people', ['email' => $email], [
            'name' => $name,
            'email' => $email,
            'department' => $department,
            'role_title' => $roleTitle,
            'is_active' => true,
        ]);
    }

    private function user(string $password, int $personId): int
    {
        return $this->upsert('users', ['person_id' => $personId], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'person_id' => $personId,
            'preferred_locale' => 'en',
            'is_active' => true,
        ]);
    }

    private function seedRoles(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->upsert('roles', ['name' => $role->value], [
                'name' => $role->value,
                'description' => ucfirst($role->value) . ' role',
            ]);
        }
    }

    private function assignRole(int $userId, string $roleName): int
    {
        $roleId = (int) query('roles')->select()->whereField('name', $roleName)->first()['id'];

        return $this->upsert('user_roles', ['user_id' => $userId, 'role_id' => $roleId], [
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    private function component(string $name, string $slug, int $typeId, int $statusId, ?int $criticalityId, int $businessOwner, int $technicalOwner, int $deployment, int $environment, string $purpose): int
    {
        return $this->upsert('components', ['slug' => $slug], [
            'name' => $name,
            'short_name' => $name,
            'slug' => $slug,
            'component_type_id' => $typeId,
            'status_id' => $statusId,
            'criticality_id' => $criticalityId,
            'business_owner_id' => $businessOwner,
            'technical_owner_id' => $technicalOwner,
            'deployment_location_id' => $deployment,
            'environment_id' => $environment,
            'project_name' => null,
            'started_on' => null,
            'purpose' => $purpose,
            'description' => $purpose,
            'documentation_url' => null,
            'repository_url' => null,
            'vendor' => null,
            'lifecycle_notes' => null,
            'is_external' => false,
        ]);
    }

    private function dependency(int $source, int $target, string $name, int $type, ?int $protocol, int $status, ?int $criticality, int $owner, string $data): int
    {
        return $this->upsert('dependencies', ['name' => $name], [
            'source_component_id' => $source,
            'target_component_id' => $target,
            'dependency_type_id' => $type,
            'protocol_id' => $protocol,
            'status_id' => $status,
            'criticality_id' => $criticality,
            'owner_id' => $owner,
            'name' => $name,
            'description' => $name,
            'data_description' => $data,
            'frequency' => 'Near real-time / daily depending on interface',
            'direction' => 'source_to_target',
            'authentication_method' => null,
            'documentation_url' => null,
            'technical_notes' => null,
            'is_bidirectional' => false,
        ]);
    }

    private function journey(string $name, string $slug, int $owner, int $status): int
    {
        return $this->upsert('journeys', ['slug' => $slug], [
            'name' => $name,
            'slug' => $slug,
            'description' => 'Demo customer journey from order placement to delivery.',
            'owner_id' => $owner,
            'status_id' => $status,
            'sort_order' => 1,
        ]);
    }

    private function journeyStep(int $journeyId, string $name, int $sortOrder): int
    {
        return $this->upsert('journey_steps', ['journey_id' => $journeyId, 'sort_order' => $sortOrder], [
            'journey_id' => $journeyId,
            'name' => $name,
            'description' => null,
            'sort_order' => $sortOrder,
        ]);
    }

    private function journeyStepComponent(int $stepId, int $componentId, string $role): int
    {
        return $this->upsert('journey_step_components', ['journey_step_id' => $stepId, 'component_id' => $componentId], [
            'journey_step_id' => $stepId,
            'component_id' => $componentId,
            'role_in_step' => $role,
            'notes' => null,
        ]);
    }

    /** @param array<string, mixed> $find @param array<string, mixed> $values */
    private function upsert(string $table, array $find, array $values): int
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $builder = query($table)->select();
        foreach ($find as $field => $value) {
            $builder->whereField($field, $value);
        }
        $existing = $builder->first();

        if ($existing !== null) {
            query($table)->update(...[...$values, 'updated_at' => $now])->whereField('id', $existing['id'])->execute();
            return (int) $existing['id'];
        }

        query($table)->insert([...$values, 'created_at' => $now, 'updated_at' => $now])->execute();

        $created = query($table)->select();
        foreach ($find as $field => $value) {
            $created->whereField($field, $value);
        }

        return (int) $created->first()['id'];
    }
}

