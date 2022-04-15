<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use GuzzleHttp\Client;

class GoogleRepository
{
    private Client $client;
    private const ENDPOINT = 'https://www.googleapis.com/books/v1/volumes';

    public function __construct()
    {
        $this->client = new Client($this->prepare());
    }

    private function prepare(): array
    {
        return [
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
        $request = $this->client->request('GET', $path);

        $response = json_decode($request->getBody()->getContents());

        return $response->items;
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
        $path .= '&key=' . $_ENV['GOOGLE_BOOKS_API_KEY'];
        ;

        return $path;
    }
}
