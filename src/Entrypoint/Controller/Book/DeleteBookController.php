<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\Book;

use Colybri\Library\Application\Command\Book\Delete\DeleteBookCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeleteBookController extends CommandController
{
    public function __invoke(Request $request)
    {
        $bookId = $request->attributes->get('id');

        $this->exec(
            DeleteBookCommand::fromPayload(
                Uuid::v4(),
                [
                    DeleteBookCommand::BOOK_ID_PAYLOAD => $bookId,
                ]
            )
        );

        return new JsonResponse(
            '',
            Response::HTTP_OK
        );
    }
}
