<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures;

class FixtureReference
{
    private $isLoaded;
    private $fixture;

    public function __construct(FixtureRepository $fixture)
    {
        $this->fixture = $fixture;
        $this->isLoaded = false;
    }

    public function load(): void
    {
        if (false === $this->isLoaded) {
            $this->fixture->load();
        }

        $this->isLoaded = true;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    public function dependants(): array
    {
        return $this->fixture->dependants();
    }

    public function clean(): void
    {
        $this->fixture->clean();
    }
}