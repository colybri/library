<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Book;

use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Exception\BookAlreadyExistException;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class BookCreator
{
    public function __construct(private BookRepository $repo, private AuthorFinder $authorFinder)
    {
    }
    public function execute(Uuid $id, BookTitle $title, ?BookSubtitle $subtitle, BookAuthorIds $authorIds, BookAuthorIsPseudo $isPseudo, BookPublishYear $publishYear, BookPublishYearIsEstimated $publishYearIsEstimated, BookIsOnWishList $isOnWishList): Book
    {

        /**
         * @var Uuid $authorId
         */
        foreach ($authorIds as $authorId) {
            $this->ensureAuthorExist($authorId);
        }

        $this->ensureBookDoesNonExist($id);

        $book = Book::create($id, $title, $subtitle, $authorIds, $isPseudo, $publishYear, $publishYearIsEstimated, $isOnWishList);
        $this->repo->insert($book);
        return $book;
    }

    public function ensureBookDoesNonExist(Uuid $id): void
    {
        if (null !== $this->repo->find($id)) {
            throw new BookAlreadyExistException(sprintf('Book whit id:%s already exist on repository', $id));
        }
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException
     */
    private function ensureAuthorExist(Uuid $id): void
    {
        $this->authorFinder->execute($id);
    }
}
