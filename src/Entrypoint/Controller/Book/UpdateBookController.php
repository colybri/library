<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Book;

use Colybri\Library\Application\Command\Book\Update\UpdateBookCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UpdateBookController extends CommandController
{
    public function __invoke(Request $request)
    {
        $body = $this->getRequestBody($request);

        $this->exec(
            UpdateBookCommand::fromPayload(
                Uuid::v4(),
                [
                    UpdateBookCommand::BOOK_ID_PAYLOAD => $body->get(UpdateBookCommand::BOOK_ID_PAYLOAD),
                    UpdateBookCommand::BOOK_TITLE_PAYLOAD => $body->get(UpdateBookCommand::BOOK_TITLE_PAYLOAD),
                    UpdateBookCommand::BOOK_SUBTITLE_PAYLOAD => $body->get(UpdateBookCommand::BOOK_SUBTITLE_PAYLOAD),
                    UpdateBookCommand::BOOK_AUTHORS_PAYLOAD => $body->get(UpdateBookCommand::BOOK_AUTHORS_PAYLOAD),
                    UpdateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => $body->get(UpdateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD),
                    UpdateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $body->get(UpdateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD),
                    UpdateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => $body->get(UpdateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD),
                    UpdateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => $body->get(UpdateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD),
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_OK
        );
    }
}
