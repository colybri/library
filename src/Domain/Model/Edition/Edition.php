<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Edition;

use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

final class Edition extends AggregateRoot
{
    private const NAME = 'edition';
    private Uuid $aggregateId;
    private AuthorFirstName $firstName;

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function jsonSerialize(): mixed
    {
        // TODO: Implement jsonSerialize() method.
    }

}