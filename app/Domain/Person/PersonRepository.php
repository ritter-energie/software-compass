<?php

declare(strict_types=1);

namespace App\Domain\Person;

interface PersonRepository
{
    public function findById(int $id): ?Person;

    /**
     * @return Person[]
     */
    public function all(): array;

    /**
     * @return Person[] Only people flagged as active, for owner dropdowns.
     */
    public function allActive(): array;

    public function save(Person $person): Person;

    public function delete(int $id): void;
}

