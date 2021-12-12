<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateAuthorController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreateAuthorCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateAuthorCommand::ID_PAYLOAD => $body->get(CreateAuthorCommand::ID_PAYLOAD),
                    CreateAuthorCommand::FIRST_NAME_PAYLOAD => $body->get(CreateAuthorCommand::FIRST_NAME_PAYLOAD)
                ]
            )
        );

        return new JsonResponse(
            '', Response::HTTP_OK
        );
    }
}