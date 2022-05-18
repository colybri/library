<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book;

use Colybri\Library\Domain\Model\Book\Event\BookCreated;
use Colybri\Library\Domain\Model\Book\Event\BookDeleted;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookFullName;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Forkrefactor\Ddd\Domain\Model\SimpleAggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class Book extends SimpleAggregateRoot implements \JsonSerializable
{
    private const NAME = 'book';
    private Uuid $aggregateId;
    private BookTitle $title;
    private BookSubtitle $subtitle;
    private BookFullName $fullName;
    private BookAuthorIds $authorIds;
    private BookPublishYear $publishYear;
    private BookPublishYearIsEstimated $publishYearIsEstimated;
    private BookAuthorIsPseudo $isPseudo;
    private BookIsOnWishList $isOnWishList;


    //https://es.wikipedia.org/wiki/Library_of_Congress_Control_Number
    //https://developer.api.oclc.org/access-options
    //https://developers.google.com/books/docs/v1/using
    //https://www.googleapis.com/books/v1/volumes/_ojXNuzgHRcC
    public static function create(
        Uuid $id,
        BookTitle $title,
        ?BookSubtitle $subtitle,
        BookAuthorIds $authorIds,
        BookAuthorIsPseudo $isPseudo,
        BookPublishYear $publishYear,
        BookPublishYearIsEstimated $publishYearIsEstimated,
        BookIsOnWishList $isOnWishList
    ) {
        $self = new self($id);
        $self->aggregateId = $id;
        $self->title = $title;
        $self->subtitle = $subtitle;
        $self->authorIds = $authorIds;
        $self->isPseudo = $isPseudo;
        $self->publishYear = $publishYear;
        $self->publishYearIsEstimated = $publishYearIsEstimated;
        $self->isOnWishList = $isOnWishList;

        $self->recordThat(BookCreated::from($id, $title, $subtitle, $authorIds, $isPseudo, $publishYear, $publishYearIsEstimated, $isOnWishList));
        return $self;
    }

    public static function hydrate(
        Uuid $id,
        BookTitle $title,
        ?BookSubtitle $subtitle,
        BookAuthorIds $authorIds,
        BookAuthorIsPseudo $isPseudo,
        BookPublishYear $publishYear,
        BookPublishYearIsEstimated $publishYearIsEstimated,
        BookIsOnWishList $isOnWishList
    ) {
        $self = new self($id);
        $self->aggregateId = $id;
        $self->title = $title;
        $self->subtitle = $subtitle;
        $self->authorIds = $authorIds;
        $self->isPseudo = $isPseudo;
        $self->publishYear = $publishYear;
        $self->publishYearIsEstimated = $publishYearIsEstimated;
        $self->isOnWishList = $isOnWishList;

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

    public function subtitle(): ?BookSubtitle
    {
        return $this->subtitle;
    }

    public function fullName(): BookFullName
    {
        $fullName = null === $this->subtitle() ? $this->title() : $this->title()->value() . ": " . $this->subtitle()->value();
        return BookFullName::from($fullName);
    }

    public function delete(BookRepository $repository)
    {
        $repository->delete($this->aggregateId());

        $this->recordThat(BookDeleted::from($this->aggregateId()));
    }

    public function authorIds(): BookAuthorIds
    {
        return $this->authorIds;
    }

    public function publishYear(): BookPublishYear
    {
        return $this->publishYear;
    }

    public function publishYearIsEstimated(): bool
    {
        return $this->publishYearIsEstimated->value();
    }

    public function authorIsPseudo(): bool
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
            'subtitle' => $this->subtitle(),
            'authorIsPseudo' =>  $this->authorIsPseudo(),
            'publishYear' => $this->publishYear(),
            'publishYearIsEstimated' => $this->publishYearIsEstimated(),
            'isOnWishList' => $this->isOnWishList()
        ];
    }
}
