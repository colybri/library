<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book;

use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookHaveEstimatedPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Forkrefactor\Ddd\Domain\Model\SimpleAggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class Book extends SimpleAggregateRoot implements \JsonSerializable
{
    private const NAME = 'book';
    private Uuid $aggregateId;
    private BookTitle $title;
    private BookAuthorIds $authorIds;
    private BookPublishYear $publishYear;
    private BookHaveEstimatedPublishYear $bookHaveEstimatedPublishYear;
    private BookAuthorIsPseudo $isPseudo;
    private BookIsOnWishList $isOnWishList;


    //https://es.wikipedia.org/wiki/Library_of_Congress_Control_Number
    //https://developer.api.oclc.org/access-options
    //https://developers.google.com/books/docs/v1/using
    //https://www.googleapis.com/books/v1/volumes/_ojXNuzgHRcC
    public static function create(
        Uuid $id,
        BookPublishYear $publishYear,
        BookHaveEstimatedPublishYear $bookHaveEstimatedPublishYear,
        BookAuthorIsPseudo $isPseudo,
        BookIsOnWishList $isOnWishList
    ) {
        $self = new self($id);
        $self->aggregateId = $id;
        $self->publishYear = $publishYear;
        $self->bookHaveEstimatedPublishYear = $bookHaveEstimatedPublishYear;
        $self->isPseudo = $isPseudo;
        $self->isOnWishList = $isOnWishList;

        return $self;
    }

    public static function hydrate(
        BookTitle $title,
    ): self {
        $self = new self(Uuid::v4());
        $self->title = $title;
        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function title(): BookTitle
    {
        return $this->title;
    }

    public function publishYear(): BookPublishYear
    {
        return $this->publishYear;
    }

    public function haveEstimatedPublishYear(): bool
    {
        return $this->bookHaveEstimatedPublishYear->value();
    }

    public function isPseudo(): bool
    {
        return $this->isPseudo->value();
    }

    public function isOnWishList(): bool
    {
        return $this->isOnWishList->value();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->aggregateId(),
            'title' => $this->title(),

        ];
    }
}
