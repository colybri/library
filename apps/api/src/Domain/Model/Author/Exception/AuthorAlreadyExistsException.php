<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author\Exception;

use Colybri\Library\Domain\Exception\ExistsException;

final class AuthorAlreadyExistsException extends ExistsException
{
}
