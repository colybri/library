<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\EditionResource;

/**
 * Where the files of the resources live (S3 compatible storage). The
 * metadata lives in EditionResourceRepository.
 */
interface EditionResourceStorage
{
    /** Copies the local file at $path into the storage */
    public function save(EditionResource $resource, string $path): void;

    /** @return resource A readable stream with the content of the file */
    public function read(EditionResource $resource);

    public function delete(EditionResource $resource): void;
}
