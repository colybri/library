<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Query\Edition\Get\GetEditionQuery;
use Colybri\Library\Entrypoint\Controller\QueryController;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\Request;

final class GetEditionController extends QueryController
{
    public function __invoke(Request $request)
    {
        $editionId = $request->attributes->get('id');

        $edition = $this->ask(
            GetEditionQuery::fromPayload(
                Uuid::v4(),
                [
                    GetEditionQuery::EDITION_ID_PAYLOAD => $editionId,
                ]
            )
        );

        return $this->response($edition);
    }
}
