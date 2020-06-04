<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus\Event;

use Acme\Common\Infrastructure\Bus\CallableFirstParameterExtractor;

final class DomainEventMapping
{
    private iterable $subscribers;

    public function __construct(iterable $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    public function eventClassFromName(string $eventName): string
    {
        foreach (CallableFirstParameterExtractor::forCallables($this->subscribers) as $eventClass => $callable) {
            $name = call_user_func(sprintf('%s::name', $eventClass));
            if ($eventName === $name) {
                return $eventClass;
            }
        }

        throw new \RuntimeException(sprintf('Unknown event for type %s', $eventName));
    }
}
