<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Query\Book\Match\MatchBookQuery;
use Colybri\Library\Application\Query\Edition\Seek\SeekEditionQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SeekEditionController extends QueryController
{
    public function __invoke(Request $request)
    {

        $result = $this->ask(
            SeekEditionQuery::fromPayload(
                Uuid::v4(),
                [
                    SeekEditionQuery::EDITION_TITLE_PAYLOAD => $request->query->get(SeekEditionQuery::EDITION_TITLE_PAYLOAD, null),
                    SeekEditionQuery::EDITION_AUTHOR_PAYLOAD => $request->query->get(SeekEditionQuery::EDITION_AUTHOR_PAYLOAD, null),
                    SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD => $request->query->get(SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD, null),
                    SeekEditionQuery::EDITION_ISBN_PAYLOAD => $request->query->get(SeekEditionQuery::EDITION_ISBN_PAYLOAD, null),
                ],
            ),
        );

        $response = new JsonResponse($result);

        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        return $response;
    }
}
