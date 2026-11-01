<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Book\Exception;

use Colybri\Library\Domain\Exception\NotFoundException;

final class BookDoesNotExistException extends NotFoundException
{
}
