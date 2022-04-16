<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Country\Get;

use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\Service\Country\CountryFinder;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class GetCountryQueryHandler implements MessageHandlerInterface
{
    public function __construct(private CountryFinder $finder)
    {
    }

    public function __invoke(GetCountryQuery $query): Country
    {
        return $this->finder->execute(
            $query->countryId()
        );
    }
}
