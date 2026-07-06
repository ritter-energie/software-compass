<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\AlterTableStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\IntegerStatement;

/** Adds team master data and optional team ownership fields next to person owners. */
final class AddTeamsAndTeamOwners implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_06_000001_add_teams_and_team_owners';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new CreateTableStatement('teams')
                ->primary()
                ->varchar('name', 255)
                ->text('description', nullable: true)
                ->datetime('created_at')
                ->datetime('updated_at')
                ->unique('name'),
            (new AlterTableStatement('components'))
                ->add(new IntegerStatement('business_owner_team_id', nullable: true))
                ->add(new IntegerStatement('technical_owner_team_id', nullable: true)),
            (new AlterTableStatement('dependencies'))
                ->add(new IntegerStatement('owner_team_id', nullable: true)),
            (new AlterTableStatement('journeys'))
                ->add(new IntegerStatement('owner_team_id', nullable: true)),
        );
    }

    public function down(): QueryStatement
    {
        return new CompoundStatement(
            (new AlterTableStatement('journeys'))->dropColumn('owner_team_id'),
            (new AlterTableStatement('dependencies'))->dropColumn('owner_team_id'),
            (new AlterTableStatement('components'))
                ->dropColumn('technical_owner_team_id')
                ->dropColumn('business_owner_team_id'),
            new DropTableStatement('teams'),
        );
    }
}

