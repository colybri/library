<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Publisher;

use Colybri\Library\Application\Command\Publisher\Delete\DeletePublisherCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DeletePublisherController extends CommandController
{
    public function __invoke(Request $request)
    {
        $publisherId = $request->attributes->get('id');

        $this->exec(
            DeletePublisherCommand::fromPayload(
                Uuid::v4(),
                [
                    DeletePublisherCommand::PUBLISHER_ID_PAYLOAD => $publisherId,
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_OK
        );
    }
}
