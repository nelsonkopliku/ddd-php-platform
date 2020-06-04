<?php

declare(strict_types=1);

namespace Acme\Common\Domain\Aggregate;

abstract class AggregateRoot
{
    use ProducesEvents;
}
