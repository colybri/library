<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Book;

use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class BookDeleter
{
    public function __construct(private BookRepository $bookRepository, private BookFinder $finder)
    {
    }

    public function execute(Uuid $id): Book
    {
        $book = $this->finder->execute($id);

        $book->delete($this->bookRepository);

        return $book;
    }
}
