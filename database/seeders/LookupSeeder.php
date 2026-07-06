<?php

declare(strict_types=1);

namespace Database\Seeders;

use DateTimeImmutable;
use Tempest\Database\DatabaseSeeder;
use UnitEnum;

use function Tempest\Database\query;

/** Seeds stable lookup/master-data values required by the MVP. */
final class LookupSeeder implements DatabaseSeeder
{
    public function run(null|string|UnitEnum $database): void
    {
        $this->seedLookup('component_types', [
            'Application', 'API', 'Database', 'Middleware', 'SaaS', 'File Share',
            'Message Queue', 'Manual Process', 'Reporting', 'Data Warehouse',
            'Integration Platform', 'Website', 'Mobile App', 'Legacy System', 'Other',
        ]);

        $this->seedLookup('component_statuses', [
            'Idea', 'In Review', 'Planned', 'In Implementation', 'Active', 'Deprecated',
            'Replacement Planned', 'Retired', 'Rejected',
        ], withSortOrder: true);

        $this->seedLookup('criticality_levels', ['Low', 'Medium', 'High', 'Business Critical'], withSortOrder: true);
        $this->seedLookup('environments', ['Development', 'Test', 'Staging', 'Production', 'Sandbox', 'Unknown']);
        $this->seedLookup('dependency_types', [
            'REST API', 'SOAP API', 'Webhook', 'Database Access', 'File Transfer', 'SFTP',
            'CSV Import', 'CSV Export', 'Message Queue', 'Manual Transfer', 'Email', 'ETL',
            'Replication', 'Shared Library', 'Other',
        ]);
        $this->seedLookup('communication_protocols', [
            'HTTP', 'HTTPS', 'REST', 'SOAP', 'SFTP', 'FTP', 'SQL', 'JDBC', 'ODBC',
            'SMTP', 'IMAP', 'AMQP', 'MQTT', 'Kafka', 'File System', 'Manual', 'Other',
        ]);
        $this->seedDataObjects();
        $this->seedDeploymentLocations();
    }

    /** @param string[] $names */
    private function seedLookup(string $table, array $names, bool $withSortOrder = false): void
    {
        foreach ($names as $index => $name) {
            $this->upsertByName($table, $name, $withSortOrder ? ['sort_order' => $index + 1] : []);
        }
    }

    private function seedDataObjects(): void
    {
        foreach (['Customer Data', 'Order Data', 'Invoice Data', 'Product Data', 'Price Data', 'Contract Data', 'Delivery Data', 'Payment Data', 'Support Ticket Data', 'User Account Data', 'Analytics Data'] as $name) {
            $this->upsertByName('data_objects', $name, [
                'contains_personal_data' => in_array($name, ['Customer Data', 'Payment Data', 'User Account Data'], true),
                'contains_sensitive_data' => in_array($name, ['Payment Data', 'User Account Data'], true),
            ]);
        }
    }

    private function seedDeploymentLocations(): void
    {
        foreach ([
            ['On-Premise Data Center', 'On-Premise'],
            ['Hosted SaaS', 'Hosted SaaS'],
            ['Public Cloud', 'Public Cloud'],
            ['Unknown', 'Unknown'],
        ] as [$name, $type]) {
            $this->upsertByName('deployment_locations', $name, ['location_type' => $type]);
        }
    }

    /** @param array<string, mixed> $values */
    private function upsertByName(string $table, string $name, array $values = []): int
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $existing = query($table)->select()->whereField('name', $name)->first();

        if ($existing !== null) {
            query($table)->update(...[...$values, 'updated_at' => $now])->whereField('id', $existing['id'])->execute();

            return (int) $existing['id'];
        }

        query($table)->insert([
            'name' => $name,
            ...$values,
            'created_at' => $now,
            'updated_at' => $now,
        ])->execute();

        return (int) query($table)->select()->whereField('name', $name)->first()['id'];
    }
}

