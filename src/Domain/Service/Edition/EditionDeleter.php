<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class EditionDeleter
{
    public function __construct(private EditionRepository $repository, private EditionFinder $finder)
    {
    }

    public function execute(Uuid $id): Edition
    {
        $edition = $this->finder->execute($id);

        $edition->delete($this->repository);

        return $edition;
    }
}
