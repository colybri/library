<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Query\Country\Get\GetCountryQuery;
use Colybri\Library\Application\Query\Edition\Get\GetEditionQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;

final class GetEditionController extends QueryController
{
    public function __invoke(Request $request)
    {
        $countryId = $request->attributes->get('id');

        $country = $this->ask(
            GetEditionQuery::fromPayload(
                Uuid::v4(),
                [
                    GetCountryQuery::COUNTRY_ID_PAYLOAD => $countryId,
                ]
            )
        );

        return $this->response($country);
    }
}
