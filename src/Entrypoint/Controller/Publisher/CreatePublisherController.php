<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Publisher;

use Colybri\Library\Application\Command\Publisher\Create\CreatePublisherCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreatePublisherController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreatePublisherCommand::fromPayload(
                Uuid::v4(),
                [
                    CreatePublisherCommand::PUBLISHER_ID_PAYLOAD => $body->get(CreatePublisherCommand::PUBLISHER_ID_PAYLOAD),
                    CreatePublisherCommand::PUBLISHER_NAME_PAYLOAD => $body->get(CreatePublisherCommand::PUBLISHER_NAME_PAYLOAD),
                    CreatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $body->get(CreatePublisherCommand::PUBLISHER_CITY_PAYLOAD),
                    CreatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => $body->get(CreatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD),
                    CreatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $body->get(CreatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
