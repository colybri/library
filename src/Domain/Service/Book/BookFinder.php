<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Book;

use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class BookFinder
{
    public function __construct(private BookRepository $repo)
    {
    }

    /**
     * @throws BookDoesNotExistException
     */
    public function execute(Uuid $id): Book
    {
        $book = $this->repo->find($id);

        $this->ensureBookExist($book);

        return $book;
    }

    public function ensureBookExist(?Book $book): void
    {
        if (null === $book) {
            throw new BookDoesNotExistException(sprintf('Book whit id:%s does not exist on repository', $book));
        }
    }
}
