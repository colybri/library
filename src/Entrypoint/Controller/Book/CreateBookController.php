<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Book;

use Colybri\Library\Application\Command\Book\Create\CreateBookCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreateBookController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            CreateBookCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateBookCommand::BOOK_ID_PAYLOAD => $body->get(CreateBookCommand::BOOK_ID_PAYLOAD),
                    CreateBookCommand::BOOK_TITLE_PAYLOAD => $body->get(CreateBookCommand::BOOK_TITLE_PAYLOAD),
                    CreateBookCommand::BOOK_AUTHORS_PAYLOAD => $body->get(CreateBookCommand::BOOK_AUTHORS_PAYLOAD),
                    CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $body->get(CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD),
                    CreateBookCommand::BOOK_IS_ESTIMATED_PUBLISH => $body->get(CreateBookCommand::BOOK_IS_ESTIMATED_PUBLISH),
                    CreateBookCommand::BOOK_PUBLISHER_ID_PAYLOAD => $body->get(CreateBookCommand::BOOK_PUBLISHER_ID_PAYLOAD),
                    CreateBookCommand::BOOK_IS_PSEUDO_PAYLOAD => $body->get(CreateBookCommand::BOOK_IS_PSEUDO_PAYLOAD),

                    CreateBookCommand::EDITION_YEAR_PAYLOAD => $body->get(CreateBookCommand::EDITION_YEAR_PAYLOAD),
                    CreateBookCommand::EDITION_GOOGLE_ID_PAYLOAD => $body->get(CreateBookCommand::EDITION_GOOGLE_ID_PAYLOAD),
                    CreateBookCommand::EDITION_ISBN_PAYLOAD => $body->get(CreateBookCommand::EDITION_ISBN_PAYLOAD),
                    CreateBookCommand::EDITION_TITLE_PAYLOAD => $body->get(CreateBookCommand::EDITION_TITLE_PAYLOAD),
                    CreateBookCommand::EDITION_IS_ON_LIBRARY => $body->get(CreateBookCommand::EDITION_IS_ON_LIBRARY),
                    CreateBookCommand::EDITION_LANGUAGE_PAYLOAD => $body->get(CreateBookCommand::EDITION_LANGUAGE_PAYLOAD),
                    CreateBookCommand::EDITION_IMAGE_PAYLOAD => $body->get(CreateBookCommand::EDITION_IMAGE_PAYLOAD),
                    CreateBookCommand::EDITION_RESOURCES_PAYLOAD => $body->get(CreateBookCommand::EDITION_RESOURCES_PAYLOAD),
                    CreateBookCommand::EDITION_CONDITION_PAYLOAD => $body->get(CreateBookCommand::EDITION_CONDITION_PAYLOAD),
                    CreateBookCommand::EDITION_PAGES_PAYLOAD => $body->get(CreateBookCommand::EDITION_PAGES_PAYLOAD),
                    CreateBookCommand::EDITION_CITY_PAYLOAD => $body->get(CreateBookCommand::EDITION_CITY_PAYLOAD)
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
