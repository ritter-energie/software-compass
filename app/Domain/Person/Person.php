<?php

declare(strict_types=1);

namespace App\Domain\Person;

use InvalidArgumentException;

/**
 * A person who can act as business owner, technical owner, interface owner
 * or journey owner.
 */
final class Person {
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $email,
        private ?string $department,
        private ?string $roleTitle,
        private bool $isActive,
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('A person name must not be blank.');
        }
    }

    public function id(): ?int {
        return $this->id;
    }

    public function name(): string {
        return $this->name;
    }

    public function email(): ?string {
        return $this->email;
    }

    public function department(): ?string {
        return $this->department;
    }

    public function roleTitle(): ?string {
        return $this->roleTitle;
    }

    public function isActive(): bool {
        return $this->isActive;
    }
}
