<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Command\Edition\Update\UpdateEditionCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateEditionController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            UpdateEditionCommand::fromPayload(
                Uuid::v4(),
                [
                    UpdateEditionCommand::EDITION_ID_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_ID_PAYLOAD),
                    UpdateEditionCommand::EDITION_YEAR_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_YEAR_PAYLOAD),
                    UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD),
                    UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD),
                    UpdateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD),
                    UpdateEditionCommand::EDITION_ISBN_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_ISBN_PAYLOAD),
                    UpdateEditionCommand::EDITION_TITLE_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_TITLE_PAYLOAD),
                    UpdateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_SUBTITLE_PAYLOAD),
                    UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD),
                    UpdateEditionCommand::EDITION_IMAGE_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_IMAGE_PAYLOAD),
                    UpdateEditionCommand::EDITION_PAGES_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_PAGES_PAYLOAD),
                    UpdateEditionCommand::EDITION_CITY_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_CITY_PAYLOAD),
                    UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD),
                    UpdateEditionCommand::EDITION_CONDITION_PAYLOAD => $body->get(UpdateEditionCommand::EDITION_CONDITION_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_NO_CONTENT
        );
    }
}
