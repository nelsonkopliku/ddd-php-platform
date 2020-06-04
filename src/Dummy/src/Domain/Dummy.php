<?php

declare(strict_types=1);

namespace Acme\Dummy\Domain;

class Dummy
{
    private string $id;

    private string $someProperty;

    private string $someOtherProperty;

    public function __construct(string $someProperty, string $someOtherProperty)
    {
        $this->someProperty = $someProperty;
        $this->someOtherProperty = $someOtherProperty;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function someProperty(): string
    {
        return $this->someProperty;
    }

    public function someOtherProperty(): string
    {
        return $this->someOtherProperty;
    }
}
