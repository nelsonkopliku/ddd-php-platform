<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus;

use ReflectionClass;
use ReflectionMethod;

final class CallableFirstParameterExtractor
{
    public static function extract($class): ?string
    {
        $reflectionClass = new ReflectionClass($class);
        $method = $reflectionClass->getMethod('__invoke');

        if (self::hasAtLeaseOneParameter($method)) {
            return self::firstParameterFqcn($method);
        }

        throw new \RuntimeException('Unable to determine first parameter for callable '.$class);
    }

    public static function forCallables(iterable $callables): iterable
    {
        foreach ($callables as $callable) {
            yield self::extract($callable) => $callable;
        }
    }

    private static function firstParameterFqcn(ReflectionMethod $method): string
    {
        return $method->getParameters()[0]->getClass()->getName();
    }

    private static function hasAtLeaseOneParameter(ReflectionMethod $method): bool
    {
        return 0 < $method->getNumberOfParameters();
    }
}
