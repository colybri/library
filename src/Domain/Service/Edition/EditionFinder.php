<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionFinder
{
    public function __construct(private EditionRepository $repo)
    {
    }

    /**
     * @throws EditionDoesNotExistException
     */
    public function execute(Uuid $id): Edition
    {
        $edition = $this->repo->find($id);

        $this->ensureEditionExist($edition);

        return $edition;
    }

    public function ensureEditionExist(?Edition $edition): void
    {
        if (null === $edition) {
            throw new EditionDoesNotExistException(sprintf('Edition whit id:%s does not exist on repository', $edition));
        }
    }
}
