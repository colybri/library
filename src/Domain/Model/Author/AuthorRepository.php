<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Author;

interface AuthorRepository
{
    public function insert(Author $author): void;
}