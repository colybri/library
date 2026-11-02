<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres;

use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\DomainEventMapping;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

class PostgresDomainEventsConsumer
{
    private const ATOM = 'Y-m-d H:i:s.uP';
    public function __construct(protected Connection $connectionWrite, private readonly DomainEventMapping $eventMapping)
    {
    }

    public function consume(int $eventsToConsume): void
    {
        $sql = "
            SELECT * from events_pending_queue
             ORDER BY ocurred_on ASC LIMIT :eventsToConsume
        ";
        $query = $this->connectionWrite->prepare($sql);
        $query->bindValue('eventsToConsume', $eventsToConsume, ParameterType::INTEGER);

        $events = $query->executeQuery()->fetchAllAssociative();
        $ids = [];

        foreach ($events as $event) {
            $domainEventClass = $this->eventMapping->eventFor($event['name']);

            $domainEvent = $domainEventClass::fromPayload(
                Uuid::from((string) $event['id']),
                Uuid::from((string) $event['agregate_id']),
                DateTimeValueObject::from((string) $event['ocurred_on']),
                \json_decode((string) $event['body'], true, 512, JSON_THROW_ON_ERROR)
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
            INSERT into events_failed_queue (
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
        $statement->bindValue('agregateId', $event->aggregateId()->value());
        $statement->bindValue('body', \json_encode($event->messagePayload(), JSON_THROW_ON_ERROR));


        $statement->executeStatement();
    }
}
