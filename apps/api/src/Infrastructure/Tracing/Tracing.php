<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Tracing;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\TracerInterface;

/**
 * Entry point to the OpenTelemetry tracer of the application code.
 *
 * Globals returns the SDK configured from the OTEL_* environment when
 * OTEL_PHP_AUTOLOAD_ENABLED is true (PHP-FPM and api_consumers), and a no-op
 * tracer otherwise (tests, console commands), so calling it is always safe.
 */
final class Tracing
{
    public const INSTRUMENTATION_NAME = 'colybri/library';

    public static function tracer(): TracerInterface
    {
        return Globals::tracerProvider()->getTracer(self::INSTRUMENTATION_NAME);
    }
}
