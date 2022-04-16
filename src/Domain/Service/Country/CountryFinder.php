<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Country;

use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Model\Country\Exception\CountryDoesNotExistException;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class CountryFinder
{
    private CountrySearcher $service;

    public function __construct(CountrySearcher $service)
    {
        $this->service = $service;
    }

    /**
     * @throws CountryDoesNotExistException
     */
    public function execute(Uuid $id): Country
    {
        $country = $this->service->execute($id);

        $this->ensureCountryExist($country);

        return $country;
    }

    public function ensureCountryExist(?Country $country): void
    {
        if (null === $country) {
            throw new CountryDoesNotExistException(sprintf('Country whit id:%s does not exist on repository', $country));
        }
    }
}
