<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Tracing;

use Colybri\Library\Domain\Messaging\ProcessorRepository;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

/**
 * Decorates the processed-messages store (Redis) with one span per call.
 * There is no OpenTelemetry auto-instrumentation for the redis extension.
 *
 * A message that a subscriber already processed is marked on the consumer
 * span with library.message.duplicate = true, so Kibana can count the
 * duplicates that were skipped per subscriber.
 */
final class TracedProcessorRepository implements ProcessorRepository
{
    public const ATTRIBUTE_DUPLICATE  = 'library.message.duplicate';
    public const ATTRIBUTE_SUBSCRIBER = 'library.message.subscriber';

    public function __construct(private readonly ProcessorRepository $inner, private readonly TracerInterface $tracer)
    {
    }

    public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName): bool
    {
        $consumerSpan = Span::getCurrent();

        $notProcessed = $this->traced('EXISTS', $subscriberClassName, fn (): bool => $this->inner->isNotAlreadyProcessed($messageId, $subscriberClassName));

        $consumerSpan->setAttribute(self::ATTRIBUTE_SUBSCRIBER, $subscriberClassName);
        $consumerSpan->setAttribute(self::ATTRIBUTE_DUPLICATE, !$notProcessed);

        return $notProcessed;
    }

    public function processSubscriber(Uuid $messageId, string $subscriberClassName): void
    {
        $this->traced('SET', $subscriberClassName, fn () => $this->inner->processSubscriber($messageId, $subscriberClassName));
    }

    /**
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    private function traced(string $command, string $subscriberClassName, callable $operation): mixed
    {
        $span = $this->tracer
            ->spanBuilder($command)
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute('db.system.name', 'redis')
            // Older name of the same attribute, the one the APM Server maps to span.subtype
            ->setAttribute('db.system', 'redis')
            ->setAttribute('db.operation.name', $command)
            ->setAttribute(self::ATTRIBUTE_SUBSCRIBER, $subscriberClassName)
            ->startSpan();
        $scope = $span->activate();

        try {
            return $operation();
        } catch (\Throwable $error) {
            $span->recordException($error);
            $span->setStatus(StatusCode::STATUS_ERROR, $error->getMessage());

            throw $error;
        } finally {
            $scope->detach();
            $span->end();
        }
    }
}
