<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Country;

use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\CountryRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class CountrySearcher
{
    public function __construct(private CountryRepository $repo)
    {
    }

    public function execute(Uuid $id): ?Country
    {
        return $this->repo->find($id);
    }
}
