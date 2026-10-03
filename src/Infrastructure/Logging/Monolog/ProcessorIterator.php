<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Logging\Monolog;

use Monolog\Processor\ProcessorInterface;

final class ProcessorIterator implements ProcessorInterface
{
    private $processors;

    public function __construct(ProcessorInterface ...$processors)
    {
        $this->processors = $processors;
    }

    public function __invoke(array $record): array
    {

        foreach ($this->processors as $processor) {
            $record = $processor($record);
        }

        return $record;
    }
}
