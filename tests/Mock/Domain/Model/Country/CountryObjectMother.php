<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\Country;

use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryNationality;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class CountryObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?CountryName $name = null,
        private ?CountryAlpha2Code $code = null,
        private ?CountryNationality $nacionality = null
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->name = $name ?? CountryName::from('Papua New Guinea');
        $this->code = $code ?? CountryAlpha2Code::from('PG');
        $this->nacionality = $nacionality ?? CountryNationality::from('Papuan');
    }


    public function build(): Country
    {
        return Country::hydrate(
            $this->id,
            $this->name,
            $this->code,
            $this->nacionality
        );
    }
}
