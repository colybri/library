<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Country;

use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class CountrySearcher
{
    private CountryRepository $repo;

    public function __construct(CountryRepository $repo)
    {
        $this->repo = $repo;
    }

    public function execute(Uuid $id): ?Country
    {
        return $this->repo->find($id);
    }
}
