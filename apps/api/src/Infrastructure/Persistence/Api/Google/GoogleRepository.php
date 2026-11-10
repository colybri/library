<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Infrastructure\Tracing\GuzzleTracingMiddleware;
use Colybri\Library\Infrastructure\Tracing\Tracing;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;

class GoogleRepository
{
    private Client $client;
    private const ENDPOINT = 'https://www.googleapis.com/books/v1/volumes';

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client($this->prepare());
    }

    private function prepare(): array
    {
        // Every call to Google Books is a span in the trace of the request
        $handler = HandlerStack::create();
        $handler->push(new GuzzleTracingMiddleware(Tracing::tracer()), 'tracing');

        return [
            'handler' => $handler,
            'base_uri' => self::ENDPOINT,
            'debug' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'timeout' => 10.0,
        ];
    }

    protected function get($path)
    {
        try {
            $request = $this->client->request('GET', $path);
        } catch (GuzzleException $exception) {
            // The message of a Guzzle exception holds the request URI with the
            // API key. Rethrow without it, and without the original exception
            // as previous, so the key reaches neither the logs nor the traces.
            throw new GoogleBooksUnavailable(
                GuzzleTracingMiddleware::redactedMessage($exception->getMessage()),
                (int) $exception->getCode()
            );
        }

        $response = json_decode($request->getBody()->getContents());

        return ($response->totalItems ?? 0) > 0 ? ($response->items ?? []) : [];
    }

    protected function byCriteria(string $path, Criteria $criteria, EntityMap $map)
    {
        $path = (new CriteriaGoogleBookAdapter($path, $map))->build($criteria);

        return $this->get($this->buildPath($path));
    }

    private function buildPath($path, array $params = []): string
    {

        foreach ($params as $key => $value) {
            $path .= $key . '=' . $value . '&';
        }
        $path .= '&key=' . ($_ENV['GOOGLE_BOOKS_API_KEY'] ?? '');

        return $path;
    }
}
