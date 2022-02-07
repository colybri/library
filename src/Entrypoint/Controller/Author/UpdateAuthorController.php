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
                    UpdateAuthorCommand::ID_PAYLOAD => $body->get(UpdateAuthorCommand::ID_PAYLOAD),
                    UpdateAuthorCommand::NAME_PAYLOAD => $body->get(UpdateAuthorCommand::NAME_PAYLOAD)
                ]
            )
        );

        return new JsonResponse(
            '', Response::HTTP_OK
        );
    }
}