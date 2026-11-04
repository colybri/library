<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Storage\Flysystem;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceStorage;
use League\Flysystem\FilesystemOperator;

/** Files of the edition resources in the resources.storage Flysystem storage (config/packages/flysystem.yaml) */
final class FlysystemEditionResourceStorage implements EditionResourceStorage
{
    public function __construct(private readonly FilesystemOperator $resourcesStorage)
    {
    }

    public function save(EditionResource $resource, string $path): void
    {
        $stream = fopen($path, 'rb');
        if (false === $stream) {
            throw new \RuntimeException(sprintf('Unable to read the uploaded file %s', $path));
        }

        try {
            // Streamed: a 200 MB file never sits whole in memory
            $this->resourcesStorage->writeStream($resource->storageKey(), $stream, [
                'mimetype' => $resource->mediaType()->value(),
            ]);
        } finally {
            if (\is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function read(EditionResource $resource)
    {
        return $this->resourcesStorage->readStream($resource->storageKey());
    }

    public function delete(EditionResource $resource): void
    {
        $this->resourcesStorage->delete($resource->storageKey());
    }
}
