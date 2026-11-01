<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Messaging;

use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

interface ProcessorRepository
{
    public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName): bool;

    public function processSubscriber(Uuid $messageId, string $subscriberClassName): void;
}