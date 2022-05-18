<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller;

use Forkrefactor\Ddd\Application\Command;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class CommandController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function getRequestBody(Request $request): ParameterBag
    {
        $body = json_decode($request->getContent(), true);
        if (empty($body)) {
            $body = json_decode(json_encode($request->request->all(), JSON_THROW_ON_ERROR), true);
        }
        return new ParameterBag($body);
    }

    protected function exec(Command $cmd)
    {
        $this->commandBus->dispatch($cmd);
    }
}
