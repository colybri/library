<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Logging;

use Colybri\Library\Infrastructure\Logging\Monolog\Processor\TraceContextProcessor;
use Colybri\Library\Tests\Infrastructure\Tracing\InMemoryTracing;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TraceContextProcessorTest extends TestCase
{
    #[Test]
    public function it_adds_the_active_trace_and_span_ids(): void
    {
        $span = (new InMemoryTracing())->tracer()->spanBuilder('request')->startSpan();
        $scope = $span->activate();

        try {
            $record = (new TraceContextProcessor())($this->record());
        } finally {
            $scope->detach();
            $span->end();
        }

        self::assertSame(['id' => $span->getContext()->getTraceId()], $record->extra['trace']);
        self::assertSame(['id' => $span->getContext()->getSpanId()], $record->extra['span']);
    }

    #[Test]
    public function it_adds_nothing_without_an_active_span(): void
    {
        $record = (new TraceContextProcessor())($this->record());

        self::assertArrayNotHasKey('trace', $record->extra);
        self::assertArrayNotHasKey('span', $record->extra);
    }

    private function record(): LogRecord
    {
        return new LogRecord(new \DateTimeImmutable(), 'app', Level::Info, 'message');
    }
}
