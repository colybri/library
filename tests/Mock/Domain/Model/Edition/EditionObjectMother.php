<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
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
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?EditionYear $year = null,
        private ?Uuid $publisherId = null,
        private ?Uuid $bookId = null,
        private ?EditionGoogleBooksId $googleId = null,
        private ?EditionISBN $isbn = null,
        private ?EditionTitle $title = null,
        private ?EditionSubtitle $subtitle = null,
        private ?EditionLocale $locale = null,
        private ?EditionImageUrl $image = null,
        private ?EditionCity $city = null,
        private ?EditionPages $pages = null,
        private ?EditionIsOnLibrary $isOnLibrary = null,
        private ?EditionCondition $condition = null
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->year = $year ?? EditionYear::from(random_int(701, 2020));
        $this->publisherId = $publisherId ?? Uuid::v4();
        $this->bookId = $bookId ?? Uuid::v4();
        $this->googleId = $googleId ?? EditionGoogleBooksId::from('dajDk3Dfy');
        $this->isbn = $isbn ?? EditionISBN::from('9780195003994');
        $this->title = $title ?? EditionTitle::from('Paideia');
        $this->subtitle = $this->subtitle ?? EditionSubtitle::from('Los ideales de la cultura griega');
        $this->locale = $locale ?? EditionLocale::from('es');
        $this->image = $image ?? EditionImageUrl::from('padeia-los-ideales');
        $this->city = $city ?? EditionCity::from('Mexico D.F.');
        $this->pages = $pages ?? EditionPages::from(random_int(70, 300));
        $this->isOnLibrary = $this->isOnLibrary ?? EditionIsOnLibrary::from(true);
        $this->condition = $condition ?? EditionCondition::from('new');
    }

    public function create(): Edition
    {
        return Edition::create(
            $this->id,
            $this->year,
            $this->publisherId,
            $this->bookId,
            $this->googleId,
            $this->isbn,
            $this->title,
            $this->subtitle,
            $this->locale,
            $this->image,
            $this->pages,
            $this->city,
            $this->isOnLibrary,
            $this->condition
        );
    }

    public function build(): Edition
    {
        return Edition::reconstitute(
            $this->id,
            $this->year,
            $this->publisherId,
            $this->bookId,
            $this->googleId,
            $this->isbn,
            $this->title,
            $this->subtitle,
            $this->locale,
            $this->image,
            $this->pages,
            $this->city,
            $this->isOnLibrary,
            $this->condition
        );
    }
}
