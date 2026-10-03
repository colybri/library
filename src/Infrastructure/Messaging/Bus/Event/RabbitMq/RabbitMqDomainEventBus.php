<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq;

use Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres\PostgresDomainEventBus;
use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\EventBus;

final class RabbitMqDomainEventBus implements EventBus
{

    public function __construct(private string $exchangeName, private RabbitMqConnection $connection, private readonly PostgresDomainEventBus $failoverPublisher)
    {
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            try {
                $this->publishEvent($event);
            } catch (\AMQPException $error) {
                $this->failoverPublisher->dispatch($event);
            }
        }
    }

    private function publishEvent(DomainEvent $event): void
    {
        $body = json_encode([
            'data' => [
                'id'          => $event->messageId(),
                'type'        => $event::messageName(),
                'occurred_on' => $event->occurredOn(),
                'attributes'  => $event->jsonSerialize(),
            ],
            'meta' => [],
        ]);

        $routingKey = $event::messageName();
        $messageId  = $event->messageId();

        $this->connection->exchange($this->exchangeName)->publish(
            $body,
            $routingKey,
            AMQP_NOPARAM,
            [
                'message_id'       => $messageId,
                'content_type'     => 'application/json',
                'content_encoding' => 'utf-8',
            ]
        );
    }
}