<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;

/** Stores lightweight application-wide settings such as network display name. */
final class CreateAppSettingsTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_03_000011_create_app_settings_table';

    public function up(): QueryStatement
    {
        return (new CreateTableStatement('app_settings'))
            ->primary()
            ->varchar('setting_key', 100)
            ->text('setting_value', nullable: true)
            ->datetime('created_at')
            ->datetime('updated_at')
            ->unique('setting_key');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('app_settings');
    }
}

