<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\EditionResource;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\EditionResource\Create\CreateEditionResourceCommand;
use Colybri\Library\Entrypoint\Controller\CommandController;
use League\MimeTypeDetection\FinfoMimeTypeDetector;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Uploads a file (multipart/form-data, field "file") to an edition. The media
 * type is detected from the content, not taken from the client.
 */
final class CreateEditionResourceController extends CommandController
{
    public const FILE_FIELD = 'file';

    public function __invoke(Request $request): Response
    {
        $file = $request->files->get(self::FILE_FIELD);

        if (!$file instanceof UploadedFile) {
            throw new InvalidArgumentException(
                'The request has no "file" field. Send it as multipart/form-data, at most 200 MB.',
                0,
                self::FILE_FIELD,
                null,
            );
        }

        if (!$file->isValid()) {
            throw new InvalidArgumentException($file->getErrorMessage(), 0, self::FILE_FIELD, null);
        }

        $path = $file->getPathname();
        $name = $request->request->get(CreateEditionResourceCommand::NAME_PAYLOAD);

        $this->exec(
            CreateEditionResourceCommand::fromPayload(
                Uuid::v4(),
                [
                    CreateEditionResourceCommand::RESOURCE_ID_PAYLOAD => $request->request->get(CreateEditionResourceCommand::RESOURCE_ID_PAYLOAD),
                    CreateEditionResourceCommand::EDITION_ID_PAYLOAD => $request->attributes->get('id'),
                    CreateEditionResourceCommand::NAME_PAYLOAD => null === $name || '' === trim((string) $name)
                        ? $file->getClientOriginalName()
                        : $name,
                    CreateEditionResourceCommand::FILE_NAME_PAYLOAD => $file->getClientOriginalName(),
                    CreateEditionResourceCommand::MEDIA_TYPE_PAYLOAD => (new FinfoMimeTypeDetector())->detectMimeTypeFromFile($path),
                    CreateEditionResourceCommand::SIZE_PAYLOAD => $file->getSize(),
                    CreateEditionResourceCommand::PATH_PAYLOAD => $path,
                ]
            )
        );

        return new JsonResponse('', Response::HTTP_CREATED);
    }
}
