<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\EditionResource;

use Colybri\Library\Application\Query\EditionResource\List\ListEditionResourcesQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ListEditionResourcesController extends QueryController
{
    public function __invoke(Request $request): Response
    {
        $resources = $this->ask(
            ListEditionResourcesQuery::fromPayload(
                Uuid::v4(),
                [
                    ListEditionResourcesQuery::EDITION_ID_PAYLOAD => $request->attributes->get('id'),
                ]
            )
        );

        return $this->response($resources);
    }
}
