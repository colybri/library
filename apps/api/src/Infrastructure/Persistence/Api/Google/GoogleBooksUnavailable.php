<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google;

/** The call to Google Books failed. The message never contains the API key. */
final class GoogleBooksUnavailable extends \RuntimeException
{
}
