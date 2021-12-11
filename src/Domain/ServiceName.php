<?php

namespace Colybri\Library\Domain;

use PcComponentes\TopicGenerator\Service;

class ServiceName extends Service
{
    private const NAME = 'colybri';

    public function name(): string
    {
        return self::NAME;
    }

}