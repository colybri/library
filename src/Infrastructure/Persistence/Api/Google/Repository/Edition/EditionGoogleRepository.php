<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google\Repository\Edition;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionImageUrl;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionSubtitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Colybri\Library\Infrastructure\Exception\RepositoryMethodNotImplementException;
use Colybri\Library\Infrastructure\Persistence\Api\Google\GoogleRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

final class EditionGoogleRepository extends GoogleRepository implements EditionRepository
{
    public function find(Uuid $id): ?Edition
    {
        throw new RepositoryMethodNotImplementException('This repository not implement method ' . __FUNCTION__);
    }

    public function match(Criteria $criteria): array
    {
        $books = $this->byCriteria('?q=', $criteria, new EditionGoogleMap());

        return \array_map(
            fn($book) => $this->map($book),
            $books
        );
    }

    public function insert(Edition $edition): void
    {
        throw new RepositoryMethodNotImplementException('This repository not implement method ' . __FUNCTION__);
    }

    public function delete(Uuid $id): void
    {
        throw new RepositoryMethodNotImplementException('This repository not implement method ' . __FUNCTION__);
    }

    private function map(\stdClass $book): Edition
    {
        return Edition::hydrate(
            Uuid::v4(),
            isset($book->volumeInfo->publishedDate) ? EditionYear::from((int)$book->volumeInfo->publishedDate) : EditionYear::from(3000),
            Uuid::v4(),
            Uuid::v4(),
            EditionGoogleBooksId::from($book->id),
            EditionISBN::from($book->volumeInfo->industryIdentifiers[0]->identifier),
            EditionTitle::from($book->volumeInfo->title),
            isset($book->volumeInfo->subtitle) ? EditionSubtitle::from($book->volumeInfo->subtitle) : null,
            EditionLocale::from($book->volumeInfo->language),
            isset($book->volumeInfo->imageLinks) ? EditionImageUrl::from($book->volumeInfo->imageLinks->thumbnail) : null,
            null,
            null,
            null,
            isset($book->volumeInfo->pageCount) ? EditionPages::from((int)$book->volumeInfo->pageCount) : null,
            EditionCity::from(''),
            EditionIsOnLibrary::from(false)
        );
    }
}
