<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Publisher;

use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class PublisherUpdater
{
    public function __construct(private PublisherRepository $repo, private PublisherFinder $finder)
    {
    }

    public function execute(Uuid $id, PublisherName $name, ?PublisherCity $city, Uuid $countryId, ?PublisherFoundationYear $foundation): Publisher
    {
        $this->ensurePublisherExist($id);

        $publisher = Publisher::hydrate($id, $name, $city, $countryId, $foundation);

        $this->repo->update($publisher);

        return $publisher;
    }
    private function ensurePublisherExist(Uuid $id): void
    {
        $this->finder->execute($id);
    }
}
