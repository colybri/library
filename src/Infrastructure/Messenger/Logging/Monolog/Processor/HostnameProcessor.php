<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messenger\Logging\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;

final class HostnameProcessor implements ProcessorInterface
{
    private string $host;

    public function __construct()
    {
        $this->host = \gethostname();
    }

    public function __invoke(array $record): array
    {
        $record['extra']['hostname'] = $this->host;

        return $record;
    }
}
