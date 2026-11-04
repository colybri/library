<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\EditionResource;

use Colybri\Library\Application\Command\EditionResource\Delete\DeleteEditionResourceCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteEditionResourceController extends CommandController
{
    public function __invoke(Request $request): Response
    {
        $this->exec(
            DeleteEditionResourceCommand::fromPayload(
                Uuid::v4(),
                [
                    DeleteEditionResourceCommand::RESOURCE_ID_PAYLOAD => $request->attributes->get('resourceId'),
                    DeleteEditionResourceCommand::EDITION_ID_PAYLOAD => $request->attributes->get('id'),
                ]
            )
        );

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }
}
