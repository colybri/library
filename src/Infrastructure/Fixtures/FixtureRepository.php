<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures;

interface FixtureRepository
{
    public function load(): void;

    public function dependants(): array;

    public function clean(): void;
}
