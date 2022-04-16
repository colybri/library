<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\Publisher;

use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class PublisherObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?PublisherName $name = null,
        private ?PublisherCity $city = null,
        private ?Uuid $countryId = null,
        private ?PublisherFoundationYear $foundtion = null
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->name = $name ?? PublisherName::from('Editorial Publicaciones de la Abadía de Montserrat');
        $this->city = $city ?? PublisherCity::from('Montserrat');
        $this->countryId = $countryId ?? Uuid::v4();
        $this->foundtion = $foundtion ?? PublisherFoundationYear::from(random_int(1498, 2025));
    }

    public function create(): Publisher
    {
        return Publisher::create(
            $this->id,
            $this->name,
            $this->city,
            $this->countryId,
            $this->foundtion
        );
    }

    public function build(): Publisher
    {
        return Publisher::hydrate(
            $this->id,
            $this->name,
            $this->city,
            $this->countryId,
            $this->foundtion
        );
    }
}
