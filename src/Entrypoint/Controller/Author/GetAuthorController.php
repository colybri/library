<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Query\Author\Get\GetAuthorQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;

class GetAuthorController extends QueryController
{
    public function __invoke(Request $request)
    {
        $authorId = $request->attributes->get('id');

        $author = $this->ask(
            GetAuthorQuery::fromPayload(
                Uuid::v4(),
                [
                    GetAuthorQuery::ID_PAYLOAD => $authorId,
                ]
            )
        );

        return $this->response($author);
    }
}