<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messenger\Middleware;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final class DoctrineTransactionalMiddleware implements MiddlewareInterface
{
    private $connection;

    public function __construct(Connection $connectionWrite)
    {
        $this->connection = $connectionWrite;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->connection->beginTransaction();

        try {
            $envelope = $stack->next()->handle($envelope, $stack);
            $this->connection->commit();

            return $envelope;
        } catch (\Throwable $exception) {
            $this->connection->rollback();

            throw $exception;
        }
    }
}
