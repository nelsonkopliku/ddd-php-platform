<?php

declare(strict_types=1);

namespace Acme\Common\Infrastructure\Bus\Event;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\MessageDecodingFailedException;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Acme\Common\Domain\Event\DomainEvent;

final class DomainEventSerializer implements SerializerInterface
{
    private DomainEventMapping $domainEventMapping;

    public function __construct(DomainEventMapping $domainEventMapping)
    {
        $this->domainEventMapping = $domainEventMapping;
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        if (empty($encodedEnvelope['body'])) {
            throw new MessageDecodingFailedException('Encoded envelope should have at least a "body".');
        }

        try {
            $decoded = json_decode($encodedEnvelope['body'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new MessageDecodingFailedException(sprintf('Could not decode message: %s.', $exception->getMessage()), $exception->getCode(), $exception);
        }

        $eventType = $encodedEnvelope['properties']['enqueue.topic'] ?? '';

        try {
            $domainEventClass = $this->domainEventMapping->eventClassFromName($eventType);
        } catch (\Throwable $exception) {
            throw new MessageDecodingFailedException(sprintf('Unable to locate event class for event type: %s', $eventType));
        }

        $event = call_user_func(
            sprintf('%s::fromPayload', $domainEventClass),
            $decoded,
            $encodedEnvelope['headers']['timestamp'] ?? time()
        );

        $stamps = [];
        // todo: will there be stamps to deserialize?
//        if (isset($headers['stamps'])) {
//            $stamps = unserialize($headers['stamps']);
//        }

        return Envelope::wrap($event, $stamps);
    }

    public function encode(Envelope $envelope): array
    {
        /** @var DomainEvent $domainEvent */
        $domainEvent = $envelope->getMessage();

        // todo: need to serialize stamps?
//        $allStamps = [];
//        foreach ($envelope->all() as $stamps) {
//            $allStamps = array_merge($allStamps, $stamps);
//        }

        $headers = [
            'type' => $domainEvent::name(),
            'timestamp' => $domainEvent->occurredOn()->getTimestamp(),
//            'stamps' => serialize($allStamps)
        ];

        return [
            'body' => json_encode(['payload' => $domainEvent->toArray()], JSON_THROW_ON_ERROR),
            'headers' => $headers,
        ];
    }
}
