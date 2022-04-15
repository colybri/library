<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\Author;

use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class AuthorObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?AuthorName $name = null,
        private ?Uuid $countryId = null,
        private ?Uuid $isPseudonymOf = null,
        private ?AuthorBornAt $bornAt = null,
        private ?AuthorDeathAt $deathAt = null
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->name = $name ?? AuthorName::from('Publio Cornelio Tácito');
        $this->countryId = $countryId ?? Uuid::v4();
        $this->isPseudonymOf = $isPseudonymOf ?? Uuid::v4();
        $this->bornAt = $bornAt ?? AuthorBornAt::from(random_int(600, 700));
        $this->deathAt = $deathAt ?? AuthorDeathAt::from(random_int(701, 2000));
    }

    public function create(): Author
    {
        return Author::create(
            $this->id,
            $this->name,
            $this->countryId,
            $this->isPseudonymOf,
            $this->bornAt,
            $this->deathAt
        );
    }

    public function build(): Author
    {
        return Author::hydrate(
            $this->id,
            $this->name,
            $this->countryId,
            $this->isPseudonymOf,
            $this->bornAt,
            $this->deathAt
        );
    }
}
