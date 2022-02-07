<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Author;

use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteAuthorController extends CommandController
{
    public function __invoke(Request $request)
    {
        $authorId = $request->attributes->get('id');

        $this->exec(
            DeleteAuthorCommand::fromPayload(
                Uuid::v4(),
                [
                    DeleteAuthorCommand::ID_PAYLOAD => $authorId,
                ]
            )
        );

        return new JsonResponse(
            '', Response::HTTP_OK
        );
    }
}