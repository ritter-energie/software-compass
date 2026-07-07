<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\ValueObject\Slug;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase {
    public function test_it_normalizes_text_to_a_url_safe_slug(): void {
        $this->assertSame('crm', Slug::fromText('CRM')->value());
        $this->assertSame('customer-relationship-management', Slug::fromText('Customer Relationship Management')->value());
        $this->assertSame('foo-bar', Slug::fromText('  Foo_Bar!! ')->value());
    }

    public function test_it_collapses_repeated_separators(): void {
        $this->assertSame('a-b-c', Slug::fromText('A///B   C')->value());
    }

    public function test_it_rejects_text_that_normalizes_to_an_empty_string(): void {
        $this->expectException(InvalidArgumentException::class);

        Slug::fromText('###');
    }

    public function test_with_suffix_appends_a_numeric_suffix(): void {
        $slug = Slug::fromText('CRM')->withSuffix(2);

        $this->assertSame('crm-2', $slug->value());
        $this->assertSame('crm-2', (string) $slug);
    }
}
