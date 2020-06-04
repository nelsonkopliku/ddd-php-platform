<?php

declare(strict_types=1);

namespace Acme\Common\Application\Bus\Event;

use Acme\Common\Domain\Event\DomainEvent;

interface EventBus
{
    public function publish(DomainEvent ...$domainEvents): void;
}
