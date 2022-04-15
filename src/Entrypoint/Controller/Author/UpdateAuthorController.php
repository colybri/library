<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Application\Command\Author\Update\UpdateAuthorCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateAuthorController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            UpdateAuthorCommand::fromPayload(
                Uuid::v4(),
                [
                    UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $body->get(UpdateAuthorCommand::AUTHOR_ID_PAYLOAD),
                    UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => $body->get(UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD),
                    UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $body->get(CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD),
                    UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $body->get(CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD),
                    UpdateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $body->get(CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD),
                    UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $body->get(CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD)
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_OK
        );
    }
}
