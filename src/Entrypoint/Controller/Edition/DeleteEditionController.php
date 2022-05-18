<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Command\Edition\Delete\DeleteEditionCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteEditionController extends CommandController
{
    public function __invoke(Request $request)
    {
        $edition = $request->attributes->get('id');

        $this->exec(
            DeleteEditionCommand::fromPayload(
                Uuid::v4(),
                [
                    DeleteEditionCommand::EDITION_ID_PAYLOAD => $edition,
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_OK
        );
    }
}
