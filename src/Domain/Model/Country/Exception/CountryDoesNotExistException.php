<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Country\Exception;

use Colybri\Library\Domain\Exception\NotFoundException;

final class CountryDoesNotExistException extends NotFoundException
{
}
