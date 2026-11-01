<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres;

use Doctrine\DBAL\Connection;
use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\EventBus;
final class PostgresDomainEventBus implements EventBus
{
    private const ATOM = 'Y-m-d H:i:s';
    public function __construct(protected Connection $connectionWrite)
    {
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->publisher($event);
        }
    }

    private function publisher(DomainEvent $event): void
    {

        $sql = "
            INSERT into events_pending_queue (
                id, 
                name,
                ocurred_on,
                agregate_id,                 
                body
            ) VALUES (
                :id,
                :name,
                :ocurredOn,
                :agregateId,                      
                :body 
            );
        ";

        $statement = $this->connectionWrite->prepare($sql);
        $statement->bindValue('id', $event->messageId()->value());
        $statement->bindValue('name', $event::messageName());
        $statement->bindValue('ocurredOn', $event->occurredOn()->format(self::ATOM));
        $statement->bindValue('agregateId', $event->aggregateId());
        $statement->bindValue('body', $event->messagePayload());


        $statement->executeStatement();
    }
}