<?php

declare(strict_types=1);

namespace Colybri\Library\Domain;

use PcComponentes\TopicGenerator\Service;

class ServiceName extends Service
{
    private const NAME = 'library';

    public function name(): string
    {
        return self::NAME;
    }
}
