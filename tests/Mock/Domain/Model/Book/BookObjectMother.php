<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\Book;

use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class BookObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?BookTitle $title = null,
        private ?BookSubtitle $subtitle = null,
        private ?BookAuthorIds $authorIds = null,
        private ?BookAuthorIsPseudo $isPseudo = null,
        private ?BookPublishYear $publishYear = null,
        private ?BookPublishYearIsEstimated $publishYearIsEstimated = null,
        private ?BookIsOnWishList $isOnWishList = null
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->title = $title ?? BookTitle::from('Une saison de machettes');
        $this->subtitle = $subtitle ?? BookSubtitle::from('Random');
        $this->authorIds = $authorIds ?? BookAuthorIds::from([Uuid::v4()]);
        $this->isPseudo = $isPseudo ?? BookAuthorIsPseudo::from((bool)random_int(0, 1));
        $this->publishYear = $publishYear ?? BookPublishYear::from(random_int(600, 700));
        $this->publishYearIsEstimated = $publishYearIsEstimated ?? BookPublishYearIsEstimated::from((bool)random_int(0, 1));
        $this->isOnWishList = $isOnWishList ?? BookIsOnWishList::from((bool)random_int(0, 1));
    }

    public function create(): Book
    {
        return Book::create(
            $this->id,
            $this->title,
            $this->subtitle,
            $this->authorIds,
            $this->isPseudo,
            $this->publishYear,
            $this->publishYearIsEstimated,
            $this->isOnWishList
        );
    }

    public function build(): Book
    {
        return Book::hydrate(
            $this->id,
            $this->title,
            $this->subtitle,
            $this->authorIds,
            $this->isPseudo,
            $this->publishYear,
            $this->publishYearIsEstimated,
            $this->isOnWishList
        );
    }
}
