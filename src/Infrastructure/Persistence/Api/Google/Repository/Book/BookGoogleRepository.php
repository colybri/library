<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google\Repository\Book;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Infrastructure\Persistence\Api\Google\GoogleRepository;

class BookGoogleRepository extends GoogleRepository implements BookRepository
{
    public function match(Criteria $criteria): array
    {
        $books = $this->byCriteria('?q=', $criteria, new BookGoogleMap());

        return \array_map(
            fn($book) => $this->map($book),
            $books
        );
    }

    private function map(\stdClass $book): Book
    {
        return Book::hydrate(
            BookTitle::from($book->volumeInfo->title),
        );

    }
}