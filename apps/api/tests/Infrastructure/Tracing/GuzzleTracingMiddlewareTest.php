<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Tracing;

use Colybri\Library\Infrastructure\Tracing\GuzzleTracingMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GuzzleTracingMiddlewareTest extends TestCase
{
    private InMemoryTracing $tracing;

    protected function setUp(): void
    {
        $this->tracing = new InMemoryTracing();
    }

    #[Test]
    public function a_request_is_a_client_span_without_the_api_key(): void
    {
        $this->client(new Response(200))->request('GET', 'https://www.googleapis.com/books/v1/volumes?q=isbn:9788420412146&key=secret-api-key');

        $span = $this->tracing->span('GET www.googleapis.com');
        $attributes = $span->getAttributes()->toArray();

        self::assertSame(SpanKind::KIND_CLIENT, $span->getKind());
        self::assertSame(200, $attributes['http.response.status_code']);
        self::assertSame('www.googleapis.com', $attributes['server.address']);
        self::assertSame(443, $attributes['server.port']);
        self::assertStringNotContainsString('secret-api-key', $attributes['url.full']);
        self::assertStringContainsString('key=REDACTED', $attributes['url.full']);
        self::assertStringContainsString('q=isbn', $attributes['url.full']);
        self::assertSame(StatusCode::STATUS_UNSET, $span->getStatus()->getCode());
    }

    #[Test]
    public function an_error_response_marks_the_span_as_failed(): void
    {
        $this->client(new Response(503))->request('GET', 'https://www.googleapis.com/books/v1/volumes', ['http_errors' => false]);

        $span = $this->tracing->span('GET www.googleapis.com');

        self::assertSame(503, $span->getAttributes()->get('http.response.status_code'));
        self::assertSame(StatusCode::STATUS_ERROR, $span->getStatus()->getCode());
    }

    #[Test]
    public function a_connection_error_is_recorded_and_rethrown(): void
    {
        $error = new ConnectException('Could not resolve host', new Request('GET', 'https://www.googleapis.com/books/v1/volumes'));

        try {
            $this->client($error)->request('GET', 'https://www.googleapis.com/books/v1/volumes');
            self::fail('The connection error must reach the caller');
        } catch (ConnectException $thrown) {
            self::assertSame($error, $thrown);
        }

        $span = $this->tracing->span('GET www.googleapis.com');

        self::assertSame(StatusCode::STATUS_ERROR, $span->getStatus()->getCode());
        self::assertSame('Could not resolve host', $span->getStatus()->getDescription());
        self::assertCount(1, $span->getEvents());
    }

    #[Test]
    public function an_error_message_with_the_api_key_is_recorded_without_it(): void
    {
        $uri = 'https://www.googleapis.com/books/v1/volumes?q=intitle:x&&key=secret-api-key';
        $error = new ConnectException(sprintf('cURL error 28: Operation timed out (see %s)', $uri), new Request('GET', $uri));

        try {
            $this->client($error)->request('GET', $uri);
            self::fail('The connection error must reach the caller');
        } catch (ConnectException) {
        }

        $span = $this->tracing->span('GET www.googleapis.com');
        $event = $span->getEvents()[0];

        self::assertStringNotContainsString('secret-api-key', $span->getStatus()->getDescription());
        self::assertStringContainsString('key=REDACTED', $span->getStatus()->getDescription());
        self::assertSame('exception', $event->getName());
        self::assertSame(ConnectException::class, $event->getAttributes()->get('exception.type'));
        self::assertStringNotContainsString('secret-api-key', $event->getAttributes()->get('exception.message'));
    }

    #[Test]
    public function the_key_is_redacted_in_guzzle_error_messages(): void
    {
        self::assertSame(
            'Client error: `GET https://www.googleapis.com/books/v1/volumes?q=a&&key=REDACTED` resulted in a `400 Bad Request` response',
            GuzzleTracingMiddleware::redactedMessage('Client error: `GET https://www.googleapis.com/books/v1/volumes?q=a&&key=AIza-secret_1` resulted in a `400 Bad Request` response')
        );
        self::assertSame('no key here', GuzzleTracingMiddleware::redactedMessage('no key here'));
    }

    private function client(Response|\Throwable $result): Client
    {
        $stack = HandlerStack::create(new MockHandler([$result]));
        $stack->push(new GuzzleTracingMiddleware($this->tracing->tracer()));

        return new Client(['handler' => $stack]);
    }
}
