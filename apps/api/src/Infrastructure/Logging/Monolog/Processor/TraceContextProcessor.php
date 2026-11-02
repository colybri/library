<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Logging\Monolog\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use OpenTelemetry\API\Trace\Span;

/**
 * Adds the active OpenTelemetry trace to every log record, as the ECS fields
 * trace.id and span.id. Kibana uses them to jump from a trace to its logs.
 * Nothing is added when there is no active, valid span.
 */
final class TraceContextProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = Span::getCurrent()->getContext();
        if (!$context->isValid()) {
            return $record;
        }

        $record->extra['trace'] = ['id' => $context->getTraceId()];
        $record->extra['span']  = ['id' => $context->getSpanId()];

        return $record;
    }
}
