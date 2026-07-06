<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use DateTimeImmutable;

use function Tempest\Database\query;

final class MariaDbPersonRepository implements PersonRepository
{
    use ResolvesLastInsertId;

    public function findById(int $id): ?Person
    {
        $row = query('people')->select()->whereField('id', $id)->first();

        return $row ? $this->toDomain($row) : null;
    }

    public function all(): array
    {
        $rows = query('people')->select()->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function allActive(): array
    {
        $rows = query('people')->select()->whereField('is_active', true)->orderBy('name')->all();

        return array_map($this->toDomain(...), $rows);
    }

    public function save(Person $person): Person
    {
        $now = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $row = [
            'name' => $person->name(),
            'email' => $person->email(),
            'department' => $person->department(),
            'role_title' => $person->roleTitle(),
            'is_active' => $person->isActive(),
            'updated_at' => $now,
        ];

        if ($person->id() === null) {
            $row['created_at'] = $now;
            query('people')->insert($row)->execute();

            return $this->findById($this->lastInsertId());
        }

        query('people')->update(...$row)->whereField('id', $person->id())->execute();

        return $this->findById($person->id());
    }

    public function delete(int $id): void
    {
        query('people')->delete()->whereField('id', $id)->execute();
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Person
    {
        return new Person(
            id: (int) $row['id'],
            name: $row['name'],
            email: $row['email'],
            department: $row['department'],
            roleTitle: $row['role_title'],
            isActive: (bool) $row['is_active'],
        );
    }
}

