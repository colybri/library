<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Publisher;

use Colybri\Library\Domain\Model\Publisher\Exception\PublisherAlreadyExistException;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class PublisherCreator
{
    public function __construct(private PublisherRepository $publisherRepository)
    {
    }

    public function execute(Uuid $id, PublisherName $name, ?PublisherCity $city, Uuid $countryId, ?PublisherFoundationYear $foundation): Publisher
    {
        $this->ensurePublisherDoesNonExist($id);

        $publisher = Publisher::create($id, $name, $city, $countryId, $foundation);
        $this->publisherRepository->insert($publisher);
        return $publisher;
    }

    public function ensurePublisherDoesNonExist(Uuid $id): void
    {
        if (null !== $this->publisherRepository->find($id)) {
            throw new PublisherAlreadyExistException(sprintf('Publisher whit id:%s already exist on repository', $id));
        }
    }
}
