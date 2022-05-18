<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Edition;

use Colybri\Library\Application\Command\Edition\Create\CreateEditionCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateEditionController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreateEditionCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateEditionCommand::EDITION_ID_PAYLOAD => $body->get(CreateEditionCommand::EDITION_ID_PAYLOAD),
                    CreateEditionCommand::EDITION_YEAR_PAYLOAD => $body->get(CreateEditionCommand::EDITION_YEAR_PAYLOAD),
                    CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $body->get(CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD),
                    CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $body->get(CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD),
                    CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $body->get(CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD),
                    CreateEditionCommand::EDITION_ISBN_PAYLOAD => $body->get(CreateEditionCommand::EDITION_ISBN_PAYLOAD),
                    CreateEditionCommand::EDITION_TITLE_PAYLOAD => $body->get(CreateEditionCommand::EDITION_TITLE_PAYLOAD),
                    CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $body->get(CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD),
                    CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $body->get(CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD),
                    CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $body->get(CreateEditionCommand::EDITION_IMAGE_PAYLOAD),
                    CreateEditionCommand::EDITION_RESOURCES_PAYLOAD =>
                        array_map(fn(UploadedFile $file) => base64_decode($file), $request->files->get(CreateEditionCommand::EDITION_RESOURCES_PAYLOAD)),
                    CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $body->get(CreateEditionCommand::EDITION_CONDITION_PAYLOAD),
                    CreateEditionCommand::EDITION_PAGES_PAYLOAD => $body->get(CreateEditionCommand::EDITION_PAGES_PAYLOAD),
                    CreateEditionCommand::EDITION_CITY_PAYLOAD => $body->get(CreateEditionCommand::EDITION_CITY_PAYLOAD),
                    CreateEditionCommand::EDITION_IS_ON_LIBRARY => $body->get(CreateEditionCommand::EDITION_IS_ON_LIBRARY)
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
