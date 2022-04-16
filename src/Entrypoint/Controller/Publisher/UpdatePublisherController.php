<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Publisher;

use Colybri\Library\Application\Command\Publisher\Update\UpdatePublisherCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdatePublisherController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            UpdatePublisherCommand::fromPayload(
                Uuid::v4(),
                [
                    UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD => $body->get(UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD),
                    UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => $body->get(UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD),
                    UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $body->get(UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD),
                    UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => $body->get(UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD),
                    UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $body->get(UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
