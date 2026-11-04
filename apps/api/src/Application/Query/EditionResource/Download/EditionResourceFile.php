<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\EditionResource\Download;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;

/** Metadata of a resource and a readable stream with its content */
final class EditionResourceFile
{
    /** @param resource $stream */
    public function __construct(public readonly EditionResource $resource, public readonly mixed $stream)
    {
    }
}
