<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition\Exception;

use Colybri\Library\Domain\Exception\NotFoundException;

final class EditionDoesNotExistException extends NotFoundException
{
}
