<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Country;

use Colybri\Library\Domain\Model\Country\ValueObject\CountryAlpha2Code;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryName;
use Colybri\Library\Domain\Model\Country\ValueObject\CountryNationality;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class Country extends AggregateRoot
{
    private const NAME = 'country';
    private CountryName $name;
    private CountryAlpha2Code $code;
    private CountryNationality $nationality;

    public static function hydrate(
        Uuid               $id,
        CountryName        $name,
        CountryAlpha2Code  $code,
        CountryNationality $nationality
    ): self
    {
        $self = new self($id);
        $self->name = $name;
        $self->code = $code;
        $self->nationality = $nationality;
        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function name(): CountryName
    {
        return $this->name;
    }

    public function alpha2Code(): CountryAlpha2Code
    {
        return $this->code;
    }

    public function nationality(): CountryNationality
    {
        return $this->nationality;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->aggregateId(),
            'name' => $this->name(),
            'alpha2Code' => $this->alpha2Code(),
            'nationality' => $this->nationality(),
        ];
    }


}