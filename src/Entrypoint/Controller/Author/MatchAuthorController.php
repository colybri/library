<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Query\Author\Match\MatchAuthorQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class MatchAuthorController extends QueryController
{
    public function __invoke(Request $request)
    {

        $result = $this->ask(
            MatchAuthorQuery::fromPayload(
                Uuid::v4(),
                [
                    MatchAuthorQuery::OFFSET_PAYLOAD => $request->query->get(MatchAuthorQuery::OFFSET_PAYLOAD, 0),
                    MatchAuthorQuery::LIMIT_PAYLOAD => $request->query->get(MatchAuthorQuery::LIMIT_PAYLOAD, 50),
                    MatchAuthorQuery::KEYWORDS_PAYLOAD => $request->query->get(MatchAuthorQuery::KEYWORDS_PAYLOAD),
                ],
            ),
        );

        return $this->response($result);
    }
}