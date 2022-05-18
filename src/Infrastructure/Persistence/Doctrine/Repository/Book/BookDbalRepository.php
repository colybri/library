<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Book;

use Doctrine\DBAL\ParameterType;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Infrastructure\Adapter\Dbal\CriteriaDbalAdapter;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class BookDbalRepository extends DbalRepository implements BookRepository
{
    public function find(Uuid $id): ?Book
    {
        $sql = "
            SELECT *, array_to_json(authors_ids) as authors from books 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionRead->prepare($sql);
        $query->bindValue('id', $id->value());

        $book = $query->executeQuery()->fetchAssociative();

        if (false === $book) {
            return null;
        }

        return $this->map($book);
    }

    public function match(Criteria $criteria): array
    {
        $queryBuilder = $this->connectionRead->createQueryBuilder()
            ->select('*')->from(BookDbalMap::table());

        (new CriteriaDbalAdapter($queryBuilder, new BookDbalMap()))->build($criteria);

        $books = $queryBuilder->executeQuery()->fetchAllAssociative();

        return \array_map(
            fn($book) => $this->map($book),
            $books
        );
    }

    public function insert(Book $book): void
    {
        $sql = "
            INSERT into books (
                id, 
                title,
                subtitle,                 
                authors_ids,
                is_pseudo_author,
                publish_year,
                is_estimated_publish_year,
                is_on_wish_list
            ) VALUES (
                :id,
                :title,
                :subtitle,
                :authorsIds,
                :isPseudo,
                :publishYear,
                :publishYearIsEstimated,
                :isOnWishList
            );
        ";



        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $book->aggregateId()->value());
        $statement->bindValue('title', $book->title()->value());
        $statement->bindValue('subtitle', $book->subtitle()?->value());
        $authorIds = implode(',', json_decode(json_encode($book->authorIds())));
        $statement->bindValue('authorsIds', '{' . $authorIds . '}');
        $statement->bindValue('isPseudo', $book->authorIsPseudo(), ParameterType::BOOLEAN);
        $statement->bindValue('publishYear', $book->publishYear()->value(), ParameterType::INTEGER);
        $statement->bindValue('publishYearIsEstimated', $book->publishYearIsEstimated(), ParameterType::BOOLEAN);
        $statement->bindValue('isOnWishList', $book->isOnWishList(), ParameterType::BOOLEAN);


        $statement->executeStatement();


        $sql = "
            INSERT into books_authors (
                book_id, 
                author_id
            ) VALUES (
                :bookId,
                :authorId
            );
        ";

        foreach ($book->authorIds() as $authorId) {
            $statement = $this->connectionWrite->prepare($sql);
            $statement->bindValue('bookId', $book->aggregateId()->value());
            $statement->bindValue('authorId', $authorId->value());

            $statement->executeStatement();
        }
    }

    public function update(Book $book): void
    {
        $sql = "
            UPDATE books SET 
                title = :title,
                subtitle = :subtitle,
                authors_ids = :authorsIds,
                is_pseudo_author = :isPseudo,
                publish_year = :publishYear,
                is_estimated_publish_year = :publishYearIsEstimated,
                is_on_wish_list = :isOnWishList,
                updated_at = CURRENT_TIMESTAMP 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);

        $statement->bindValue('id', $book->aggregateId()->value());
        $statement->bindValue('title', $book->title()->value());
        $statement->bindValue('subtitle', $book->subtitle()?->value());
        $authorIds = implode(',', json_decode(json_encode($book->authorIds())));
        $statement->bindValue('authorsIds', '{' . $authorIds . '}');
        $statement->bindValue('isPseudo', $book->authorIsPseudo(), ParameterType::BOOLEAN);
        $statement->bindValue('publishYear', $book->publishYear()->value(), ParameterType::INTEGER);
        $statement->bindValue('publishYearIsEstimated', $book->publishYearIsEstimated(), ParameterType::BOOLEAN);
        $statement->bindValue('isOnWishList', $book->isOnWishList(), ParameterType::BOOLEAN);

        $statement->executeStatement();

        $sql = "
            DELETE from books_authors
             WHERE (
                book_id = :bookId
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('bookId', $book->aggregateId()->value());

        $query->executeStatement();


        $sql = "
            INSERT into books_authors (
                book_id, 
                author_id
            ) VALUES (
                :bookId,
                :authorId
            );
        ";

        foreach ($book->authorIds() as $authorId) {
            $statement = $this->connectionWrite->prepare($sql);
            $statement->bindValue('bookId', $book->aggregateId()->value());
            $statement->bindValue('authorId', $authorId->value());

            $statement->executeStatement();
        }
    }

    public function delete(Uuid $id): void
    {

        $sql = "
            DELETE from books_authors
             WHERE (
                book_id = :bookId
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('bookId', $id->value());

        $query->executeStatement();

        $sql = "
            DELETE from books 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('id', $id->value());

        $query->executeStatement();
    }


    private function map(array $book): Book
    {
        return Book::hydrate(
            Uuid::from($book['id']),
            BookTitle::from($book['title']),
            null === $book['subtitle'] ? null : BookSubtitle::from($book['subtitle']),
            BookAuthorIds::from(array_map(fn($id) => Uuid::from((string)$id), json_decode($book['authors']))),
            BookAuthorIsPseudo::from($book['is_pseudo_author']),
            BookPublishYear::from($book['publish_year']),
            BookPublishYearIsEstimated::from($book['is_estimated_publish_year']),
            BookIsOnWishList::from($book['is_on_wish_list']),
        );
    }
}
