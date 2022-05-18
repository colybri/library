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
                    CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => $body->get(CreateBookCommand::BOOK_SUBTITLE_PAYLOAD),
                    CreateBookCommand::BOOK_AUTHORS_PAYLOAD => $body->get(CreateBookCommand::BOOK_AUTHORS_PAYLOAD),
                    CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => $body->get(CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD),
                    CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $body->get(CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD),
                    CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => $body->get(CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD),
                    CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => $body->get(CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_CREATED
        );
    }
}
