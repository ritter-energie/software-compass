<?php

declare(strict_types=1);

namespace App\Shared\ValueObject;

use Stringable;

/**
 * A URL-safe, lowercase, hyphen-separated identifier derived from a name.
 *
 * Slugs are used for human-readable, stable URLs for components and
 * journeys (e.g. `/components/customer-relationship-management`).
 */
final readonly class Slug implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = $this->normalize($value);

        if ($normalized === '') {
            throw new \InvalidArgumentException('A slug cannot be empty.');
        }

        $this->value = $normalized;
    }

    public static function fromText(string $text): self
    {
        return new self($text);
    }

    /**
     * Appends a numeric suffix, used by repositories to resolve slug
     * collisions (e.g. `crm`, `crm-2`, `crm-3`, ...).
     */
    public function withSuffix(int $suffix): self
    {
        return new self("{$this->value}-{$suffix}");
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    private function normalize(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }
}

