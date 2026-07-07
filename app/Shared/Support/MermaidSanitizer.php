<?php

declare(strict_types=1);

namespace App\Shared\Support;

/**
 * Helpers for safely generating Mermaid.js flowchart syntax from
 * user-provided, untrusted strings.
 *
 * Mermaid diagrams are generated at runtime from database content. Because labels can
 * contain arbitrary text entered by users, they must be sanitized before
 * being embedded in Mermaid source, otherwise a component name or
 * dependency description could break the diagram syntax or inject
 * unintended Mermaid directives.
 */
final class MermaidSanitizer {
    private const int MAX_LABEL_LENGTH = 60;

    /**
     * Builds a stable, Mermaid-safe node identifier for a given entity type
     * and numeric id (e.g. `C42`, `S7`).
     */
    public static function nodeId(string $prefix, int $id): string {
        return $prefix . $id;
    }

    /**
     * Escapes and truncates a label so it can be safely placed inside
     * Mermaid node/edge syntax, e.g. `C1["label"]`.
     */
    public static function label(string $label): string {
        // Mermaid labels are single-line; collapse any whitespace/newlines.
        $flattened = preg_replace('/\s+/', ' ', trim($label)) ?? '';

        // Escape double quotes, since labels are wrapped in "...".
        $escaped = str_replace('"', '#quot;', $flattened);

        if (mb_strlen($escaped) > self::MAX_LABEL_LENGTH) {
            $escaped = mb_substr($escaped, 0, self::MAX_LABEL_LENGTH - 1) . '…';
        }

        return $escaped;
    }
}
