<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Command\Edition\Create\CreateEditionResourceCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateEditionResourceController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreateEditionResourceCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateEditionResourceCommand::EDITION_ID_PAYLOAD => $body->get(CreateEditionResourceCommand::EDITION_ID_PAYLOAD),
                    CreateEditionResourceCommand::EDITION_RESOURCE_PAYLOAD => $request->files->get(CreateEditionResourceCommand::EDITION_RESOURCE_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
