<?php

declare(strict_types=1);

namespace Database\Migrations;

use Tempest\Database\MigratesDown;
use Tempest\Database\MigratesUp;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropTableStatement;
use Tempest\Database\QueryStatements\OnDelete;

/**
 * Creates the `governance_reviews` table used to track the mandatory
 * duplicate-check / ownership / deployment review for new components.
 */
final class CreateGovernanceReviewsTable implements MigratesUp, MigratesDown
{
    public string $name = '2026_07_02_000007_create_governance_reviews_table';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('governance_reviews')
            ->primary()
            ->belongsTo('governance_reviews.component_id', 'components.id', onDelete: OnDelete::CASCADE)
            ->belongsTo('governance_reviews.reviewer_id', 'people.id', nullable: true)
            ->varchar('review_status', 100, default: 'open')
            ->boolean('duplicate_check_done', default: false)
            ->boolean('interface_check_done', default: false)
            ->boolean('owner_check_done', default: false)
            ->boolean('data_check_done', default: false)
            ->boolean('deployment_check_done', default: false)
            ->text('notes', nullable: true)
            ->datetime('reviewed_at', nullable: true)
            ->datetime('created_at')
            ->datetime('updated_at');
    }

    public function down(): QueryStatement
    {
        return new DropTableStatement('governance_reviews');
    }
}

