<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Logging\Monolog\Processor;

use Forkrefactor\Ddd\Domain\Model\DomainEvent;
use Monolog\Processor\ProcessorInterface;

final class OccurredOnProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        if (false === \array_key_exists('message', $record['context'])) {
            return $record;
        }

        $message = $record['context']['message'];

        if ($message instanceof DomainEvent) {
            $occurredOn = \sprintf(
                '%d%d',
                $message->occurredOn()->getTimestamp(),
                $message->occurredOn()->format('v')
            );
            $record['occurred_on'] = \intval($occurredOn);

            return $record;
        }

        $record['occurred_on'] = \intval(
            \round(
                \microtime(true) * 1000,
            )
        );

        return $record;
    }
}
