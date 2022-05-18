<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Publisher;

use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\Publisher;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class PublisherFinder
{
    public function __construct(private PublisherRepository $repo)
    {
    }

    /**
     * @throws PublisherDoesNotExistException
     */
    public function execute(Uuid $id): Publisher
    {
        $publisher = $this->repo->find($id);

        $this->ensurePublisherExist($publisher);

        return $publisher;
    }

    private function ensurePublisherExist(?Publisher $publisher): void
    {
        if (null === $publisher) {
            throw new PublisherDoesNotExistException(sprintf('Publisher whit id:%s does not exist on repository', $publisher));
        }
    }
}
