<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Book;

use Colybri\Library\Application\Query\Book\Bulk\SearchBookQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SearchBookController extends QueryController
{
    public function __invoke(Request $request)
    {

        $result = $this->ask(
            SearchBookQuery::fromPayload(
                Uuid::v4(),
                [
                    SearchBookQuery::TITLE_PAYLOAD => $request->query->get(SearchBookQuery::TITLE_PAYLOAD, null),
                    SearchBookQuery::AUTHOR_PAYLOAD => $request->query->get(SearchBookQuery::AUTHOR_PAYLOAD, null),
                    SearchBookQuery::PUBLISHER_PAYLOAD => $request->query->get(SearchBookQuery::PUBLISHER_PAYLOAD, null),
                    SearchBookQuery::SUBJECT_PAYLOAD => $request->query->get(SearchBookQuery::SUBJECT_PAYLOAD, null),
                    SearchBookQuery::ISBN_PAYLOAD => $request->query->get(SearchBookQuery::ISBN_PAYLOAD, null),
                ],
            ),
        );

        $response = new JsonResponse($result);

        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);

        return $response;
    }
}