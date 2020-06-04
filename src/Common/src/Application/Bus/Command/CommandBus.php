<?php

declare(strict_types=1);

namespace Acme\Common\Application\Bus\Command;

use Acme\Common\Domain\Command\Command;

interface CommandBus
{
    public function dispatch(Command $command): void;
}
