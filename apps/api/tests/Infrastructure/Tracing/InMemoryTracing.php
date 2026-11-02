<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Tracing;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/** A real SDK tracer that keeps the finished spans in memory, for assertions. */
final class InMemoryTracing
{
    private readonly InMemoryExporter $exporter;
    private readonly TracerProvider $provider;

    public function __construct()
    {
        $this->exporter = new InMemoryExporter();
        $this->provider = new TracerProvider(new SimpleSpanProcessor($this->exporter));
    }

    public function tracer(): TracerInterface
    {
        return $this->provider->getTracer('test');
    }

    /** @return list<SpanDataInterface> */
    public function spans(): array
    {
        return array_values(iterator_to_array($this->exporter->getSpans()));
    }

    public function span(string $name): SpanDataInterface
    {
        foreach ($this->spans() as $span) {
            if ($span->getName() === $name) {
                return $span;
            }
        }

        throw new \RuntimeException(sprintf('No span named "%s"', $name));
    }
}
