<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Country;

use Colybri\Library\Application\Query\Country\Get\GetCountryQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GetCountryController extends QueryController
{
    public function __invoke(Request $request)
    {
        $countryId = $request->attributes->get('id');

        $country = $this->ask(
            GetCountryQuery::fromPayload(
                Uuid::v4(),
                [
                    GetCountryQuery::COUNTRY_ID_PAYLOAD => $countryId,
                ]
            )
        );

        return $this->response($country);
    }
}
