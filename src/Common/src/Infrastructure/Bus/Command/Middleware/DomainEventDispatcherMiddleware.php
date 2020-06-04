<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus\Command\Middleware;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Acme\Common\Application\Bus\Event\EventBus;
use Acme\Common\Domain\Event\EventRecorder;

final class DomainEventDispatcherMiddleware implements MiddlewareInterface
{
    private EventRecorder $eventRecorder;

    private EventBus $eventBus;

    public function __construct(EventRecorder $eventRecorder, EventBus $eventBus)
    {
        $this->eventRecorder = $eventRecorder;
        $this->eventBus = $eventBus;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            $envelope = $stack->next()->handle($envelope, $stack);
        } catch (\Exception $exception) {
            $this->eventRecorder->eraseEvents();

            if ($exception instanceof HandlerFailedException) {
                throw new HandlerFailedException($exception->getEnvelope()->withoutAll(HandledStamp::class), $exception->getNestedExceptions());
            }

            throw $exception;
        }

        $this->eventBus->publish(...$this->eventRecorder->releaseEvents());

        return $envelope;
    }
}
