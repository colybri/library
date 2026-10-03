<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\Edition;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCondition;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionSubtitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Colybri\Library\Infrastructure\Exception\RepositoryMethodNotImplementException;
use Colybri\Library\Infrastructure\Persistence\Doctrine\Repository\DbalRepository;
use Doctrine\DBAL\ParameterType;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Isbn\Isbn;

final class EditionDbalRepository extends DbalRepository implements EditionRepository
{
    public function find(Uuid $id): ?Edition
    {
        $sql = "
            SELECT * from editions 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionRead->prepare($sql);
        $statement->bindValue('id', $id->value());
        $edition = $statement->executeQuery()->fetchAssociative();

        if (false === $edition) {
            return null;
        }
        return $this->map($edition);
    }

    public function match(Criteria $criteria): array
    {
        throw new RepositoryMethodNotImplementException('This repository not implement method ' . __FUNCTION__);
    }

    public function insert(Edition $edition): void
    {
        $sql = "
            INSERT into editions (
                id, 
                year,
                publisher_id,
                book_id,
                google_id,
                isbn,
                title,
                subtitle,
                locale,
                image,
                pages,
                city,
                is_on_library,
                condition
            ) VALUES (
                :id,
                :year,
                :publisherId,
                :bookId,
                :googleId,
                :isbn,
                :title,
                :subtitle,                      
                :locale,
                :image,
                :pages,
                :city,
                :isOnLibrary,
                :condition
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $edition->aggregateId()->value());
        $statement->bindValue('year', $edition->year()->value(), ParameterType::INTEGER);
        $statement->bindValue('publisherId', $edition->publisherId()->value());
        $statement->bindValue('bookId', $edition->bookId()->value());
        $statement->bindValue('googleId', $edition->googleBooksId()?->value());
        $statement->bindValue('isbn', $edition->isbn()->value(), ParameterType::INTEGER);
        $statement->bindValue('title', $edition->title()->value());
        $statement->bindValue('subtitle', $edition->subtitle()?->value());
        $statement->bindValue('locale', $edition->locale()->value());
        $statement->bindValue('image', $edition->imageSlug()?->value());
        $statement->bindValue('condition', $edition->condition()?->value());
        $statement->bindValue('pages', $edition->pages()?->value(), ParameterType::INTEGER);
        $statement->bindValue('city', $edition->city()->value());
        $statement->bindValue('isOnLibrary', $edition->isOnLibrary()->value(), ParameterType::BOOLEAN);

        $statement->executeQuery();
    }

    public function update(Edition $edition): void
    {
        $sql = "
            UPDATE editions SET 
                year = :year,
                publisher_id = :publisherId,
                book_id = :bookId,
                google_id = :googleId,
                isbn = :isbn,
                title = :title,
                subtitle = :subtitle,
                locale = :locale,
                image = :image,
                pages = :pages,
                city = :city,
                is_on_library = :isOnLibrary,
                condition = :condition,
                updated_at = CURRENT_TIMESTAMP 
             WHERE (
                id = :id
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $edition->aggregateId()->value());
        $statement->bindValue('year', $edition->year()->value(), ParameterType::INTEGER);
        $statement->bindValue('publisherId', $edition->publisherId()->value());
        $statement->bindValue('bookId', $edition->bookId()->value());
        $statement->bindValue('googleId', $edition->googleBooksId()?->value());
        $statement->bindValue('isbn', $edition->isbn()->value(), ParameterType::INTEGER);
        $statement->bindValue('title', $edition->title()->value());
        $statement->bindValue('subtitle', $edition->subtitle()?->value());
        $statement->bindValue('locale', $edition->locale()->value());
        $statement->bindValue('image', $edition->imageSlug()?->value());
        $statement->bindValue('condition', $edition->condition()?->value());
        $statement->bindValue('pages', $edition->pages()?->value(), ParameterType::INTEGER);
        $statement->bindValue('city', $edition->city()->value());
        $statement->bindValue('isOnLibrary', $edition->isOnLibrary()->value(), ParameterType::BOOLEAN);

        $statement->executeQuery();
    }


    public function delete(Uuid $id): void
    {
        $sql = "
            DELETE from editions 
             WHERE (
                id = :id
            );
        ";

        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('id', $id->value());

        $query->executeStatement();
    }

    private function map(array $edition): Edition
    {
        return Edition::hydrate(
            Uuid::from((string)$edition['id']),
            EditionYear::from((int)$edition['year']),
            Uuid::from((string)$edition['publisher_id']),
            Uuid::from((string)$edition['book_id']),
            EditionGoogleBooksId::from((string)$edition['google_id']),
            EditionISBN::from((string)$edition['isbn']),
            EditionTitle::from((string)$edition['title']),
            null === $edition['subtitle'] ? null : EditionSubtitle::from((string)$edition['subtitle']),
            EditionLocale::from((string)$edition['locale']),
            null,
            null === $edition['pages'] ? null : EditionPages::from((int)$edition['pages']),
            EditionCity::from((string)$edition['city']),
            EditionIsOnLibrary::from((bool)$edition['is_on_library']),
            null === $edition['condition'] ? null : EditionCondition::from((string)$edition['condition'])
        );
    }
}
