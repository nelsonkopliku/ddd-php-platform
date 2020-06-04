<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus\Command;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Acme\Common\Application\Bus\Command\CommandBus;
use Acme\Common\Domain\Command\Command;

final class MessengerCommandBus implements CommandBus
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->bus = $commandBus;
    }

    public function dispatch(Command $command): void
    {
        try {
//            $command = Envelope::wrap($command);
            $this->bus->dispatch($command);
        } catch (HandlerFailedException $exception) {
            // todo: better exception handling
            throw new UnrecoverableMessageHandlingException($exception->getPrevious()->getMessage());
        }
    }
}
