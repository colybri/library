<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging\Message\ValueObject;

class MessageValueObjectMultiton
{
    private static array $instances = [];

    final protected function __construct()
    {
    }

    protected static function instance(): static
    {
        $key = static::class;

        if (false === \array_key_exists($key, self::$instances)) {
            self::$instances[$key] = new static();
        }

        return self::$instances[$key];
    }
}
