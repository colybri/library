<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messenger;

use Colybri\Library\Entrypoint\Controller\MessageResultExtractor;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class LastHandledStampExtractor implements MessageResultExtractor
{
    public function extract(Envelope $message)
    {
        $stamp = $message->last(HandledStamp::class);

        if (null === $stamp) {
            return null;
        }

        return $stamp->getResult();
    }
}
