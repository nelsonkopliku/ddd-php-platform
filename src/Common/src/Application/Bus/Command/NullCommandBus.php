<?php

declare(strict_types=1);

namespace Acme\Common\Application\Bus\Command;

use Acme\Common\Domain\Command\Command;

final class NullCommandBus implements CommandBus
{
    public function dispatch(Command $command): void
    {
        // Noop
    }
}
