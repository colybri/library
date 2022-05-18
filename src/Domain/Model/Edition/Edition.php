<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition;

use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCondition;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionImageUrl;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionSubtitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class Edition extends AggregateRoot
{
    private const NAME = 'edition';

    private EditionYear $year;
    private Uuid $publisherId;
    private Uuid $bookId;
    private ?EditionGoogleBooksId $googleBooksId;
    private EditionISBN $isbn;
    private EditionTitle $title;
    private ?EditionSubtitle $subtitle;
    private EditionLocale $locale;
    private ?EditionImageUrl $imageSlug;
    private $resource;
    private $resourceTypes;
    private ?EditionCondition $condition;
    private EditionCity $city;
    private ?EditionPages $pages;
    private EditionIsOnLibrary $isOnLibrary;


    public static function create(
        Uuid $id,
        EditionYear $year,
        Uuid $publisherId,
        Uuid $bookId,
        ?EditionGoogleBooksId $googleBooksId,
        EditionISBN $isbn,
        EditionTitle $title,
        ?EditionSubtitle $subtitle,
        EditionLocale $locale,
        ?EditionImageUrl $image,
        $resource,
        $resourceTypes,
        ?EditionCondition $condition,
        ?EditionPages $pages,
        EditionCity $city,
        EditionIsOnLibrary $isOnLibrary
    ): self {
        $self = new self($id);
        $self->year = $year;
        $self->publisherId = $publisherId;
        $self->bookId = $bookId;
        $self->googleBooksId = $googleBooksId;
        $self->isbn = $isbn;
        $self->title = $title;
        $self->subtitle = $subtitle;
        $self->locale = $locale;
        $self->imageSlug = $image;
        $self->resource = $resource;
        $self->resourceTypes = $resourceTypes;
        $self->condition = $condition;
        $self->pages = $pages;
        $self->city = $city;
        $self->isOnLibrary = $isOnLibrary;

        return $self;
    }

    public static function hydrate(
        Uuid $id,
        EditionYear $year,
        Uuid $publisherId,
        Uuid $bookId,
        ?EditionGoogleBooksId $googleBooksId,
        EditionISBN $isbn,
        EditionTitle $title,
        ?EditionSubtitle $subtitle,
        EditionLocale $locale,
        ?EditionImageUrl $image,
        $resource,
        $resourceTypes,
        ?EditionCondition $condition,
        ?EditionPages $pages,
        EditionCity $city,
        EditionIsOnLibrary $isOnLibrary
    ): self {
        $self = new self($id);
        $self->year = $year;
        $self->publisherId = $publisherId;
        $self->bookId = $bookId;
        $self->googleBooksId = $googleBooksId;
        $self->isbn = $isbn;
        $self->title = $title;
        $self->subtitle = $subtitle;
        $self->locale = $locale;
        $self->imageSlug = $image;
        $self->resource = $resource;
        $self->resourceTypes = $resourceTypes;
        $self->condition = $condition;
        $self->pages = $pages;
        $self->city = $city;
        $self->isOnLibrary = $isOnLibrary;

        return $self;
    }


    public function delete(EditionRepository $repository)
    {
        $repository->delete($this->aggregateId());
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function year(): EditionYear
    {
        return $this->year;
    }

    public function publisherId(): Uuid
    {
        return $this->publisherId;
    }

    public function bookId(): Uuid
    {
        return $this->bookId;
    }

    public function googleBooksId(): ?EditionGoogleBooksId
    {
        return $this->googleBooksId;
    }

    public function isbn(): EditionISBN
    {
        return $this->isbn;
    }

    public function title(): EditionTitle
    {
        return $this->title;
    }

    public function subtitle(): ?EditionSubtitle
    {
        return $this->subtitle;
    }

    public function locale(): EditionLocale
    {
        return $this->locale;
    }

    public function imageSlug(): ?EditionImageUrl
    {
        return $this->imageSlug;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function resourceTypes()
    {
        return $this->resourceTypes;
    }

    public function condition(): ?EditionCondition
    {
        return $this->condition;
    }

    public function city(): EditionCity
    {
        return $this->city;
    }

    public function pages(): ?EditionPages
    {
        return $this->pages;
    }

    public function isOnLibrary(): EditionIsOnLibrary
    {
        return $this->isOnLibrary;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->aggregateId(),
            'title' => $this->title(),
            'subtitle' => $this->subtitle(),
            'year' => $this->year(),
            'pages' => $this->pages(),
            'language' => $this->locale(),
            'googleId' => $this->googleBooksId(),
            'isbn' => $this->isbn(),
            'city' => $this->city(),
            'publisherId' => $this->publisherId(),
            'bookId' => $this->bookId(),
            'isOnLibrary' => $this->isOnLibrary(),
            'condition' => $this->condition(),
        ];
    }
}
