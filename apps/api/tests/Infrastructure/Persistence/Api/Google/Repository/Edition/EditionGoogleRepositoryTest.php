<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Persistence\Api\Google\Repository\Edition;

use Colybri\Criteria\Domain\Conjunction;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Infrastructure\Persistence\Api\Google\Repository\Edition\EditionGoogleRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EditionGoogleRepositoryTest extends TestCase
{
    private array $history = [];

    #[Test]
    public function given_google_volumes_when_matching_then_maps_the_usable_ones_and_skips_the_rest(): void
    {
        $repository = $this->repository([
            'totalItems' => 4,
            'items' => [
                $this->volume('v1', 'es', [['type' => 'ISBN_10', 'identifier' => '0306406152'], ['type' => 'ISBN_13', 'identifier' => '9780306406157']]),
                $this->volume('v2', 'es', [['type' => 'OTHER', 'identifier' => 'UOM:39015004201234']]),
                $this->volume('v3', 'de', [['type' => 'ISBN_13', 'identifier' => '9780306406157']]),
                $this->volume('v4', 'en', null),
            ],
        ]);

        $editions = $repository->match($this->criteria('El nombre de la rosa'));

        self::assertCount(1, $editions);
        self::assertSame(9780306406157, $editions[0]->isbn()->value());
        self::assertSame('v1', $editions[0]->googleBooksId()->value());
        self::assertStringContainsString('q=intitle:El%20nombre%20de%20la%20rosa', (string) $this->history[0]['request']->getUri());
    }

    #[Test]
    public function given_no_google_results_when_matching_then_returns_nothing(): void
    {
        $repository = $this->repository(['totalItems' => 0]);

        self::assertSame([], $repository->match($this->criteria('xyz')));
    }

    private function repository(array $body): EditionGoogleRepository
    {
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], (string) \json_encode($body))]));
        $stack->push(Middleware::history($this->history));

        return new EditionGoogleRepository(new Client([
            'base_uri' => 'https://www.googleapis.com/books/v1/volumes',
            'handler' => $stack,
        ]));
    }

    private function criteria(string $title): Criteria
    {
        return new Criteria(
            Filters::from(
                Conjunction::fromFilters(
                    Filter::from(FilterField::from(BookTitle::class), FilterOperator::Contains, FilterValue::from($title))
                )
            ),
            Order::none(),
            null,
            null
        );
    }

    private function volume(string $id, string $language, ?array $identifiers): array
    {
        $info = ['title' => 'El nombre de la rosa', 'language' => $language, 'publishedDate' => '1980-01-01'];
        if (null !== $identifiers) {
            $info['industryIdentifiers'] = $identifiers;
        }

        return ['id' => $id, 'volumeInfo' => $info];
    }
}
