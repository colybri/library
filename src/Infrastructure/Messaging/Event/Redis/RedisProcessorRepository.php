<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Event\Redis;

use Colybri\Library\Domain\Messaging\ProcessorRepository;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class RedisProcessorRepository implements ProcessorRepository
{
    public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName) : bool
    {
        return true;
    }

    public function processSubscriber(Uuid $messageId, string $subscriberClassName): void
    {
        // TODO: Implement processSubscriber() method.
    }
}