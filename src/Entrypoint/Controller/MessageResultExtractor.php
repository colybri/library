<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller;

use Symfony\Component\Messenger\Envelope;

interface MessageResultExtractor
{
    public function extract(Envelope $message);

}