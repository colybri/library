<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateAuthorController extends CommandController
{

    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreateAuthorCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateAuthorCommand::ID_PAYLOAD => $body->get(CreateAuthorCommand::ID_PAYLOAD),
                    CreateAuthorCommand::FIRST_NAME_PAYLOAD => $body->get(CreateAuthorCommand::FIRST_NAME_PAYLOAD),
                    CreateAuthorCommand::LAST_NAME_PAYLOAD => $body->get(CreateAuthorCommand::LAST_NAME_PAYLOAD),
                    CreateAuthorCommand::COUNTRY_ID_PAYLOAD => $body->get(CreateAuthorCommand::COUNTRY_ID_PAYLOAD),
                    CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => $body->get(CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD),
                    CreateAuthorCommand::BORN_AT_PAYLOAD => $body->get(CreateAuthorCommand::BORN_AT_PAYLOAD),
                    CreateAuthorCommand::DEATH_AT_PAYLOAD => $body->get(CreateAuthorCommand::DEATH_AT_PAYLOAD)

                ]
            )
        );

        return new JsonResponse(
            '', Response::HTTP_CREATED
        );
    }
}