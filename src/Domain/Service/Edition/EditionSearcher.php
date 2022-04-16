<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Edition;

use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class EditionSearcher
{
    public function __construct(private EditionRepository $repo)
    {
    }

    public function execute(Uuid $id): ?Edition
    {
        return $this->repo->find($id);
    }
}
