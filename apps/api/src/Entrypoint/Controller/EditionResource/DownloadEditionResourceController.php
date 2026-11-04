<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller\EditionResource;

use Colybri\Library\Application\Query\EditionResource\Download\DownloadEditionResourceQuery;
use Colybri\Library\Application\Query\EditionResource\Download\EditionResourceFile;
use Colybri\Library\Entrypoint\Controller\QueryController;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams the file from the storage, without loading it whole in memory */
final class DownloadEditionResourceController extends QueryController
{
    public function __invoke(Request $request): Response
    {
        /** @var EditionResourceFile $file */
        $file = $this->ask(
            DownloadEditionResourceQuery::fromPayload(
                Uuid::v4(),
                [
                    DownloadEditionResourceQuery::RESOURCE_ID_PAYLOAD => $request->attributes->get('resourceId'),
                    DownloadEditionResourceQuery::EDITION_ID_PAYLOAD => $request->attributes->get('id'),
                ]
            )
        );

        $stream = $file->stream;
        $resource = $file->resource;
        $fileName = $resource->fileName()->value();

        $response = new StreamedResponse(static function () use ($stream): void {
            $output = fopen('php://output', 'wb');
            stream_copy_to_stream($stream, $output);
            fclose($output);
            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $response->headers->set('Content-Type', $resource->mediaType()->value());
        $response->headers->set('Content-Length', (string) $resource->size()->value());
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName,
            self::asciiFallback($fileName),
        ));

        return $response;
    }

    private static function asciiFallback(string $fileName): string
    {
        $ascii = preg_replace('/[^\x20-\x7e]|[%"\/\\\\]/', '_', $fileName);

        return '' === $ascii ? 'resource' : $ascii;
    }
}
