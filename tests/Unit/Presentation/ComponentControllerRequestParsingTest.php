<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation;

use App\Presentation\Http\Controller\ComponentController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tempest\Support\Arr\ImmutableArray;

final class ComponentControllerRequestParsingTest extends TestCase {
    public function test_int_list_accepts_tempest_immutable_array_values_from_multi_select_fields(): void {
        $controller = (new ReflectionClass(ComponentController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionClass(ComponentController::class)->getMethod('intList');

        $ids = $method->invoke($controller, new ImmutableArray(['2', '3', '', '2', '4']), 3);

        $this->assertSame([2, 4], $ids);
    }
}

