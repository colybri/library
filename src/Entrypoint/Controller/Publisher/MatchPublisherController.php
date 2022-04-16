<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Publisher;

use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;

class MatchPublisherController extends QueryController
{
    public function __invoke(Request $request)
    {

        $result = $this->ask(
            MatchPublisherQuery::fromPayload(
                Uuid::v4(),
                [
                    MatchPublisherQuery::OFFSET_PAYLOAD => $request->query->get(MatchPublisherQuery::OFFSET_PAYLOAD, 0),
                    MatchPublisherQuery::LIMIT_PAYLOAD => $request->query->get(MatchPublisherQuery::LIMIT_PAYLOAD, 50),
                    MatchPublisherQuery::KEYWORDS_PAYLOAD => $request->query->get(MatchPublisherQuery::KEYWORDS_PAYLOAD),
                ],
            ),
        );

        return $this->response($result);
    }
}
