<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres;

use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\DomainEventMapping;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class PostgresDomainEventsConsumer
{
    private const ATOM = 'Y-m-d H:i:s';
    public function __construct(protected Connection $connectionWrite, private readonly DomainEventMapping $eventMapping)
    {
    }

    public function consume(int $eventsToConsume): void
    {
        $sql = "
            SELECT * from events_pending_queue 
             ORDER BY occurred_on ASC LIMIT :eventsToConsume
        ";
        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('eventsToConsume', $eventsToConsume);

        $events = $query->executeQuery()->fetchAllAssociative();
        $ids = [];

        foreach ($events as $event) {
            $domainEventClass = $this->eventMapping->eventFor($event['name']);

            $domainEvent = $domainEventClass::fromPayload(
                $event['id'],
                $event['aggregate_id'],
                $event['occurred_on'],
                $event['body']
            );

            try {
                foreach ($this->eventMapping->subscribersFor($event['name']) as $subscriber) {
                    $subscriber($domainEvent);
                }
            } catch (\RuntimeException $exception) {
                $this->publishOnFailedQueue($domainEvent);
            }

            $ids[] = $event['id'];
        }

        if (!empty($ids)) {
            $this->connectionWrite->executeStatement(
                'DELETE FROM events_pending_queue WHERE id IN (:ids)',
                ['ids' => $ids],
                ['ids' => ArrayParameterType::STRING]
            );
        }
    }

    private function publishOnFailedQueue(DomainEvent $event): void
    {

        $sql = "
            INSERT into events_failded_queue (
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
