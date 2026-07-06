<?php
declare(strict_types=1);
namespace App\PHPStan;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
/** Infers SelectQueryBuilder row generics for query('table')->select(). */
final class TempestTableQueryBuilderSelectReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return QueryBuilder::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'select';
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (! $methodCall->var instanceof FuncCall || ! $methodCall->var->name instanceof Name) {
            return null;
        }
        if (! in_array((string) $methodCall->var->name, ['query', 'Tempest\\Database\\query'], true)) {
            return null;
        }
        $argument = $methodCall->var->getArgs()[0] ?? null;
        if ($argument === null) {
            return null;
        }
        $argumentType = $scope->getType($argument->value);
        if (! $argumentType->isString()->yes()) {
            return null;
        }
        foreach ($argumentType->getConstantStrings() as $constantString) {
            if (class_exists($constantString->getValue())) {
                return null;
            }
        }
        return new GenericObjectType(SelectQueryBuilder::class, [
            new ArrayType(new StringType(), new MixedType()),
        ]);
    }
}
