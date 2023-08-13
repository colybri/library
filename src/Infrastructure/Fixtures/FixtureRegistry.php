<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Fixtures;

class FixtureRegistry
{
    private $registry;

    public function __construct()
    {
        $this->registry = [];
    }

    public function addFixture(FixtureRepository $fixture)
    {
        $this->registry[get_class($fixture)] = new FixtureReference($fixture);
    }

    public function registry(): array
    {
        return $this->registry;
    }
    public function execute()
    {
        \array_walk($this->registry, [$this, 'load']);
    }

    private function load(FixtureReference $reference)
    {
        foreach ($reference->dependants() as $theDependant) {
            $this->load($this->registry[$theDependant]);
        }

        $reference->load();
    }
}
