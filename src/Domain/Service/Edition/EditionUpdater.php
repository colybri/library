<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
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

final class EditionUpdater
{
    public function __construct(private EditionRepository $repo, private EditionFinder $finder, private PublisherFinder $publisherFinder, private BookFinder $bookFinder)
    {
    }
    /**
     * @throws \Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException
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
        ?EditionPages $pages,
        EditionCity $city,
        EditionIsOnLibrary $isOnLibrary,
        ?EditionCondition $condition
    ): Edition {

        $this->ensureEditionExist($id);

        $this->ensurePublisherExist($publisherId);

        $this->ensureBookExist($bookId);

        $edition = Edition::hydrate(
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
            $pages,
            $city,
            $isOnLibrary,
            $condition
        );

        $this->repo->update($edition);

        return $edition;
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException
     */
    public function ensureEditionExist(Uuid $id): void
    {
        $this->finder->execute($id);
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException
     */
    public function ensureBookExist(Uuid $id): void
    {
        $this->bookFinder->execute($id);
    }

    /**
     * @throws \Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException
     */
    public function ensurePublisherExist(Uuid $id): void
    {
        $this->publisherFinder->execute($id);
    }
}
