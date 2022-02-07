<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Controller;

use Assert\Assertion;
use Forkrefactor\Ddd\Application\Query;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

class QueryController
{

    public function __construct(private MessageBusInterface $queryBus, private MessageResultExtractor $extractor)
    {
    }

    public function getRequestBody(Request $request): ParameterBag
    {
        Assertion::isJsonString($request->getContent());

        $body = json_decode($request->getContent(), true);

        return new ParameterBag($body);
    }

    protected function ask(Query $query): mixed
    {
        return $this->extractor->extract($this->queryBus->dispatch($query));
    }
}