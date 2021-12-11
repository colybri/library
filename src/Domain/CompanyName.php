<?php

declare(strict_types=1);

namespace Colybri\Library\Domain;

use PcComponentes\TopicGenerator\Company;

class CompanyName extends Company
{
    private const NAME = 'library';

    public function name(): string
    {
       return self::NAME;
    }

}