<?php

declare(strict_types=1);

namespace Tests\Unit\Shared;

use App\Shared\Support\MermaidSanitizer;
use PHPUnit\Framework\TestCase;

final class MermaidSanitizerTest extends TestCase
{
    public function test_node_id_combines_prefix_and_id(): void
    {
        $this->assertSame('C42', MermaidSanitizer::nodeId('C', 42));
        $this->assertSame('S7', MermaidSanitizer::nodeId('S', 7));
    }

    public function test_label_collapses_whitespace_and_newlines(): void
    {
        $this->assertSame('a b c', MermaidSanitizer::label("  a \n b\t c  "));
    }

    public function test_label_escapes_double_quotes(): void
    {
        $this->assertSame('Say #quot;hi#quot;', MermaidSanitizer::label('Say "hi"'));
    }

    public function test_label_truncates_long_text(): void
    {
        $label = MermaidSanitizer::label(str_repeat('a', 100));

        $this->assertSame(60, mb_strlen($label));
        $this->assertStringEndsWith('…', $label);
    }
}

