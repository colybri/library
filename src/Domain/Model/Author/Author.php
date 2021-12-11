<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author;

use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;


final class Author extends AggregateRoot
{
    private const NAME = 'author';
    private Uuid $aggregateId;
    private AuthorFirstName $firstName;

    public function ___construct(
        Uuid  $id,
        AuthorFirstName $firstName
    ): self
    {
        $self = new self($id);
        $self->id = $id;
        $self->firstName = $firstName;

        $self->recordThat(AuthorCreated::from($id, $firstName));
        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Uuid
    {
        return $this->aggregateId;
    }

    public function firstName(): AuthorFirstName
    {
        return $this->firstName;
    }

    public function jsonSerialize(): array
    {
        return [
            'firstName' => $this->firstName
        ];
    }

}