<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq;

use AMQPEnvelope;
use AMQPQueue;
use AMQPQueueException;
use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\DomainEventMapping;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Exchange\RabbitMqExchangeNameFormatter;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Trace\TracerProviderInterface as SdkTracerProviderInterface;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Throwable;
use Colybri\Library\Domain\Messaging\DomainEventSubscriber;

final class RabbitMqDomainEventsConsumer
{
    /** @var array<string, true> Queues that already have a consumer registered on the channel */
    private array $registeredQueues = [];

    public function __construct(
        private readonly RabbitMqConnection $connection,
        private readonly DomainEventMapping $mapping,
        private readonly string $exchangeName,
        private readonly int $maxRetries,
        private readonly TracerInterface $tracer,
        private readonly TextMapPropagatorInterface $propagator,
        private readonly TracerProviderInterface $tracerProvider,
    ) {
    }

    /**
     * Processes messages until the queue stays empty for the connection read
     * timeout. Calling it again for the same queue reuses the consumer already
     * registered (AMQP_JUST_CONSUME), so a long-running loop keeps one consumer.
     */
    public function consume(string $queueName): void
    {
        $flags = isset($this->registeredQueues[$queueName]) ? AMQP_JUST_CONSUME : AMQP_NOPARAM;
        $this->registeredQueues[$queueName] = true;

        try {
            $this->connection->queue($queueName)->consume($this->consumer(), $flags);
        } catch (AMQPQueueException $error) {
            // We don't want to raise an error if there are no messages in the queue
        }
    }

    private function consumer(): callable
    {
        return function (AMQPEnvelope $envelope, AMQPQueue $queue) {
            // One CONSUMER span per message, child of the trace that published
            // it: the publisher put traceparent in the AMQP headers.
            $span = $this->tracer
                ->spanBuilder(sprintf('%s process', $queue->getName()))
                ->setSpanKind(SpanKind::KIND_CONSUMER)
                ->setParent($this->propagator->extract($envelope->getHeaders()))
                ->setAttribute('messaging.system', 'rabbitmq')
                ->setAttribute('messaging.operation.type', 'process')
                ->setAttribute('messaging.destination.name', $queue->getName())
                ->setAttribute('messaging.message.id', (string) $envelope->getMessageId())
                ->setAttribute('messaging.rabbitmq.destination.routing_key', (string) $envelope->getRoutingKey())
                ->startSpan();
            $scope = $span->activate();

            try {
                $this->process($envelope, $queue);
            } catch (Throwable $error) {
                $span->recordException($error);
                $span->setStatus(StatusCode::STATUS_ERROR, $error->getMessage());

                throw $error;
            } finally {
                $scope->detach();
                $span->end();
                $this->flushTraces();
            }
        };
    }

    private function process(AMQPEnvelope $envelope, AMQPQueue $queue): void
    {
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
    }

    /**
     * The consumer is a long-running process: without this, the spans of a
     * message would wait for the next batch instead of reaching the APM Server.
     */
    private function flushTraces(): void
    {
        if ($this->tracerProvider instanceof SdkTracerProviderInterface) {
            $this->tracerProvider->forceFlush();
        }
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

    private function reconstituteEvent(array $eventData): DomainEvent
    {
        $eventClass = $this->mapping->eventFor($eventData['data']['type']);
        $attributes = $eventData['data']['attributes'];

        return $eventClass::fromPayload(
            Uuid::from((string) $eventData['data']['id']),
            Uuid::from((string) $attributes['aggregate_id']),
            DateTimeValueObject::from((string) $eventData['data']['occurred_on']),
            $attributes['payload'],
            (int) ($attributes['aggregate_version'] ?? 0)
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
