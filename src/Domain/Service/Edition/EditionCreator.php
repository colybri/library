<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionAlreadyExistException;
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
use Colybri\Library\Domain\Service\Book\BookFinder;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionCreator
{
    public function __construct(private EditionRepository $editionRepository, private PublisherFinder $publisherFinder, private BookFinder $bookFinder)
    {
    }

    /**
     * @throws EditionAlreadyExistException
     * @throws \Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException
     * @throws \Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException
     */
    public function execute(
        Uuid $id,
        EditionYear $year,
        Uuid $publisherId,
        Uuid $bookId,
        ?EditionGoogleBooksId $googleId,
        EditionISBN $isbn,
        EditionTitle $title,
        ?EditionSubtitle $subtitle,
        EditionLocale $locale,
        ?EditionImageUrl $image,
        $resource,
        ?EditionCondition $condition,
        ?EditionPages $pages,
        EditionCity $city,
        EditionIsOnLibrary $isOnLibrary
    ): Edition {
        $this->ensureEditionDoesNonExist($id);

        $this->ensurePublisherExist($publisherId);

        $this->ensureBookExist($bookId);

        $edition = Edition::create(
            $id,
            $year,
            $publisherId,
            $bookId,
            $googleId,
            $isbn,
            $title,
            $subtitle,
            $locale,
            $image,
            null,
            null,
            $condition,
            $pages,
            $city,
            $isOnLibrary
        );

        $this->editionRepository->insert($edition);
        return $edition;
    }

    private function ensureEditionDoesNonExist(Uuid $id): void
    {
        if (null !== $this->editionRepository->find($id)) {
            throw new EditionAlreadyExistException(sprintf('Edition whit id:%s already exist on repository', $id));
        }
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException
     */
    private function ensurePublisherExist(Uuid $id): void
    {
        $this->publisherFinder->execute($id);
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException
     */
    private function ensureBookExist(Uuid $id): void
    {
        $this->bookFinder->execute($id);
    }
}
