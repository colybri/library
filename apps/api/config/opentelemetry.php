<?php

declare(strict_types=1);

// Loaded by Composer (autoload.files) after the packages, so it replaces the
// "http" transport the OTLP exporter registered. The SDK reads the registry
// only when the first span is created, after every autoload file has run.
\OpenTelemetry\SDK\Registry::registerTransportFactory(
    'http',
    \Colybri\Library\Infrastructure\Tracing\OtlpHttpTransportFactory::class,
    true,
);
