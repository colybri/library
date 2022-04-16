<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Publisher;

use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class PublisherDeleter
{
    public function __construct(private PublisherRepository $publisherRepository, private PublisherFinder $finder)
    {
    }

    public function execute(Uuid $id): Publisher
    {
        $publisher = $this->finder->execute($id);

        $publisher->delete($this->publisherRepository);

        return $publisher;
    }
}
