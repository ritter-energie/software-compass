<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Component\ComponentRepository;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Governance\GovernanceReviewRepository;
use App\Domain\Journey\JourneyRepository;
use App\Domain\Person\PersonRepository;
use Tempest\Container\Container;
use Tempest\Container\DynamicInitializer;
use Tempest\Reflection\ClassReflector;
use UnitEnum;

/**
 * Registers repository interface bindings for Tempest's container.
 *
 * Controllers and Application Services depend on domain-level interfaces,
 * while Infrastructure owns the concrete MariaDB-backed implementations.
 *
 * A DynamicInitializer is used because a normal Initializer can only resolve
 * a single return type; here we need to map several interfaces at once.
 */
final readonly class RepositoryInitializer implements DynamicInitializer
{
    /** @var array<class-string, class-string> */
    private const array MAP = [
        ComponentRepository::class => MariaDbComponentRepository::class,
        DependencyRepository::class => MariaDbDependencyRepository::class,
        JourneyRepository::class => MariaDbJourneyRepository::class,
        PersonRepository::class => MariaDbPersonRepository::class,
        GovernanceReviewRepository::class => MariaDbGovernanceReviewRepository::class,
    ];

    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool
    {
        return array_key_exists($class->getName(), self::MAP);
    }

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object
    {
        return $container->get(self::MAP[$class->getName()]);
    }
}
