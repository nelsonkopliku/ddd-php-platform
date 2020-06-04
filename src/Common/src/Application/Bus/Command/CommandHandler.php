<?php

declare(strict_types=1);

namespace Acme\Common\Application\Bus\Command;

use Acme\Common\Domain\Command\Command;

/**
 * @method void __invoke(Command $command)
 */
interface CommandHandler
{
}
