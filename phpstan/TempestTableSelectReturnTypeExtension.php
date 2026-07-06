<?php
declare(strict_types=1);
namespace App\PHPStan;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
/** Infers raw associative row types for Tempest table-name select queries. */
final class TempestTableSelectReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return SelectQueryBuilder::class;
    }
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['all', 'first'], true);
    }
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (! $this->isTableNameSelectChain($methodCall->var, $scope) && ! $this->hasRowGeneric($scope->getType($methodCall->var))) {
            return null;
        }
        $rowType = new ArrayType(new StringType(), new MixedType());
        return match ($methodReflection->getName()) {
            'all' => new ArrayType(new IntegerType(), $rowType),
            'first' => new UnionType([$rowType, new NullType()]),
            default => null,
        };
    }
    private function isTableNameSelectChain(Expr $expression, Scope $scope): bool
    {
        $current = $expression;
        while ($current instanceof MethodCall) {
            if ((string) $current->name === 'select') {
                return $this->isTableNameQueryCall($current->var, $scope);
            }
            $current = $current->var;
        }
        return false;
    }
    private function isTableNameQueryCall(Expr $expression, Scope $scope): bool
    {
        if (! $expression instanceof FuncCall || ! $expression->name instanceof Name) {
            return false;
        }
        if (! in_array((string) $expression->name, ['query', 'Tempest\\Database\\query'], true)) {
            return false;
        }
        $argument = $expression->getArgs()[0] ?? null;
        if ($argument === null) {
            return false;
        }
        $argumentType = $scope->getType($argument->value);
        if (! $argumentType->isString()->yes()) {
            return false;
        }
        foreach ($argumentType->getConstantStrings() as $constantString) {
            if (class_exists($constantString->getValue())) {
                return false;
            }
        }
        return true;
    }
    private function hasRowGeneric(Type $type): bool
    {
        if (! $type instanceof GenericObjectType || $type->getClassName() !== SelectQueryBuilder::class) {
            return false;
        }
        $genericTypes = $type->getTypes();
        return ($genericTypes[0] ?? null) instanceof ArrayType;
    }
}
