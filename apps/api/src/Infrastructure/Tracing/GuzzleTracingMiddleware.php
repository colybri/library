<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Tracing;

use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Guzzle middleware: one CLIENT span per outgoing request (Google Books).
 *
 * Written by hand instead of the Guzzle auto-instrumentation for two reasons:
 * the query string carries the Google API key, which must not reach
 * Elasticsearch, and the auto-instrumentation would also trace the requests
 * the OTLP exporter itself sends to the APM Server through Guzzle.
 */
final class GuzzleTracingMiddleware
{
    /** Query parameters whose value is replaced before it is recorded */
    private const SECRET_QUERY_PARAMETERS = ['key'];

    public function __construct(private readonly TracerInterface $tracer)
    {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $uri  = $request->getUri();
            $span = $this->tracer
                ->spanBuilder(sprintf('%s %s', $request->getMethod(), $uri->getHost()))
                ->setSpanKind(SpanKind::KIND_CLIENT)
                ->setAttribute('http.request.method', $request->getMethod())
                ->setAttribute('server.address', $uri->getHost())
                ->setAttribute('server.port', $uri->getPort() ?? ('https' === $uri->getScheme() ? 443 : 80))
                ->setAttribute('url.full', self::redactedUrl($uri))
                ->startSpan();

            return $handler($request, $options)->then(
                static function (ResponseInterface $response) use ($span): ResponseInterface {
                    $span->setAttribute('http.response.status_code', $response->getStatusCode());
                    if ($response->getStatusCode() >= 400) {
                        $span->setStatus(StatusCode::STATUS_ERROR);
                    }
                    $span->end();

                    return $response;
                },
                static function (mixed $reason) use ($span): PromiseInterface {
                    if ($reason instanceof \Throwable) {
                        $span->recordException($reason);
                        $span->setStatus(StatusCode::STATUS_ERROR, $reason->getMessage());
                    } else {
                        $span->setStatus(StatusCode::STATUS_ERROR);
                    }
                    $span->end();

                    return Create::rejectionFor($reason);
                }
            );
        };
    }

    public static function redactedUrl(UriInterface $uri): string
    {
        parse_str($uri->getQuery(), $query);
        foreach (self::SECRET_QUERY_PARAMETERS as $name) {
            if (array_key_exists($name, $query)) {
                $query[$name] = 'REDACTED';
            }
        }

        return (string) $uri->withQuery(http_build_query($query));
    }
}
