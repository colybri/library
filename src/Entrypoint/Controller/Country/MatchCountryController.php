<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Country;

use Colybri\Library\Application\Query\Country\Match\MatchCountryQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MatchCountryController extends QueryController
{
    public function __invoke(Request $request)
    {

        $result = $this->ask(
            MatchCountryQuery::fromPayload(
                Uuid::v4(),
                [
                    MatchCountryQuery::OFFSET_PAYLOAD => $request->query->get(MatchCountryQuery::OFFSET_PAYLOAD, 0),
                    MatchCountryQuery::LIMIT_PAYLOAD => $request->query->get(MatchCountryQuery::LIMIT_PAYLOAD, 50),
                    MatchCountryQuery::KEYWORDS_PAYLOAD => $request->query->get(MatchCountryQuery::KEYWORDS_PAYLOAD),
                ],
            ),
        );

        return $this->response($result);
    }
}
