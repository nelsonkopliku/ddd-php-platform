<?php

declare(strict_types=1);

namespace Acme\Common\UI;

use Acme\Common\Application\Bus\Command\CommandBus;

abstract class DispatchesCommand
{
    protected CommandBus $bus;

    public function __construct(CommandBus $bus)
    {
        $this->bus = $bus;
    }
}
