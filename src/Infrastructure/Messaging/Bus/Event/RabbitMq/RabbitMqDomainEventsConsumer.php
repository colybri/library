<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq;

use AMQPEnvelope;
use AMQPQueue;
use AMQPQueueException;
use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\DomainEventMapping;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Exchange\RabbitMqExchangeNameFormatter;
use Throwable;
use Colybri\Library\Domain\Messaging\DomainEventSubscriber;

final class RabbitMqDomainEventsConsumer
{
    public function __construct(
        private readonly RabbitMqConnection $connection,
        private readonly DomainEventMapping $mapping,
        private readonly string             $exchangeName,
        private readonly int                $maxRetries
    )
    {
    }

    public function consume(string $queueName): void
    {
        try {
            $this->connection->queue($queueName)->consume($this->consumer());
        } catch (AMQPQueueException $error) {
            // We don't want to raise an error if there are no messages in the queue
        }
    }

    private function consumer(): callable
    {
        return function (AMQPEnvelope $envelope, AMQPQueue $queue) {

            $eventData = json_decode($envelope->getBody(), true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
            }
            $event = $this->reconstituteEvent($eventData);

            try {
                /* @var DomainEventSubscriber $subscriber */
                foreach ($this->mapping->subscribersFor($eventData['data']['type']) as $subscriber) {
                    $subscriber($event);
                }
            } catch (Throwable $error) {
                $this->handleConsumptionError($envelope, $queue);

                throw $error;
            }

            $queue->ack($envelope->getDeliveryTag());
        };
    }

    private function handleConsumptionError(AMQPEnvelope $envelope, AMQPQueue $queue): void
    {
        $this->hasOvercomeMaxRetries($envelope)
            ? $this->sendToDeadLetter($envelope, $queue)
            : $this->sendToRetry($envelope, $queue);

        $queue->ack($envelope->getDeliveryTag());
    }

    private function hasOvercomeMaxRetries(AMQPEnvelope $envelope): bool
    {
        return ($envelope->getHeaders()['redelivery_count'] ?? 0) >= $this->maxRetries;
    }

    private function sendToDeadLetter(AMQPEnvelope $envelope, AMQPQueue $queue): void
    {
        $this->sendMessageTo(RabbitMqExchangeNameFormatter::deadLetter($this->exchangeName), $envelope, $queue);
    }

    private function sendToRetry(AMQPEnvelope $envelope, AMQPQueue $queue): void
    {
        $this->sendMessageTo(RabbitMqExchangeNameFormatter::retry($this->exchangeName), $envelope, $queue);
    }

    private function reconstituteEvent($eventData): DomainEvent
    {
        $eventName = $eventData['data']['type'];
        $eventClass = $this->mapping->eventFor($eventName);
        if (null === $eventClass) {
            throw new \RuntimeException("The event <$eventName> doesn't exist or has no subscribers");
        }

        return $eventClass::fromPayload(
            $eventData['data']['id'],
            $eventData['data']['attributes']['id'],
            $eventData['data']['occurred_on'],
            $eventData['data']['attributes']
        );
    }

    private function sendMessageTo(string $exchangeName, AMQPEnvelope $envelope, AMQPQueue $queue): void
    {
        $headers = $envelope->getHeaders();
        $headers['redelivery_count'] = ($envelope->getHeaders()['redelivery_count'] ?? 0) + 1;

        $this->connection->exchange($exchangeName)->publish(
            $envelope->getBody(),
            $queue->getName(),
            AMQP_NOPARAM,
            [
                'message_id' => $envelope->getMessageId(),
                'content_type' => $envelope->getContentType(),
                'content_encoding' => $envelope->getContentEncoding(),
                'priority' => $envelope->getPriority(),
                'headers' => $headers,
            ]
        );
    }
}
