<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Logging\Monolog\Processor;

use PcComponentes\Ddd\Domain\Model\DomainEvent;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

final class OccurredOnProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        if (false === \array_key_exists('message', $record->context)) {
            return $record;
        }

        $message = $record->context['message'];

        if ($message instanceof DomainEvent) {
            $occurredOn = \sprintf(
                '%d%d',
                $message->occurredOn()->getTimestamp(),
                $message->occurredOn()->format('v')
            );
            $record->extra['occurred_on'] = \intval($occurredOn);

            return $record;
        }

        $record->extra['occurred_on'] = \intval(
            \round(
                \microtime(true) * 1000,
            )
        );

        return $record;
    }
}
