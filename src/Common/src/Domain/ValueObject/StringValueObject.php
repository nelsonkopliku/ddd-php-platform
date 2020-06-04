<?php

declare(strict_types=1);

namespace Acme\Common\Domain\ValueObject;

abstract class StringValueObject
{
    protected string $value;

    final protected function __construct(string $value)
    {
        $this->value = $value;
    }

    abstract public static function fromString(string $id): StringValueObject;

    public function value(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->value();
    }

    public function equalsTo(StringValueObject $other): bool
    {
        return $this->value === $other->value();
    }
}
