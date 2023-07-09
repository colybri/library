<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Exception;

use Colybri\Library\Domain\Exception\NotFoundException;

final class AuthorDoesNotExistException extends NotFoundException
{
}
