<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq;

use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\DomainEventSubscriber;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Exchange\RabbitMqExchangeNameFormatter;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Queue\RabbitMqQueueNameFormatter;

final class RabbitConfigurator
{
    private $connection;

    public function __construct(RabbitMqConnection $connection)
    {
        $this->connection = $connection;
    }

    public function configure(string $exchangeName, DomainEventSubscriber ...$subscribers): void
    {
        $retryExchangeName      = RabbitMqExchangeNameFormatter::retry($exchangeName);
        $deadLetterExchangeName = RabbitMqExchangeNameFormatter::deadLetter($exchangeName);

        $this->declareExchange($exchangeName);
        $this->declareExchange($retryExchangeName);
        $this->declareExchange($deadLetterExchangeName);

        foreach ($subscribers as $subscriber) {
            $this->queueDeclarator($exchangeName, $retryExchangeName, $deadLetterExchangeName, $subscriber);
        }
    }

    private function queueDeclarator(
        string $exchangeName,
        string $retryExchangeName,
        string $deadLetterExchangeName,
        DomainEventSubscriber $subscriber
    )
    {
        $queueName           = RabbitMqQueueNameFormatter::format($subscriber);
        $retryQueueName      = RabbitMqQueueNameFormatter::formatRetry($subscriber);
        $deadLetterQueueName = RabbitMqQueueNameFormatter::formatDeadLetter($subscriber);


        $queue = $this->declareQueue($queueName);
        $retryQueue = $this->declareQueue($retryQueueName, $exchangeName, $queueName, 1000);
        $deadLetterQueue = $this->declareQueue($deadLetterQueueName);

        $queue->bind($exchangeName, $queueName);
        $retryQueue->bind($retryExchangeName, $queueName);
        $deadLetterQueue->bind($deadLetterExchangeName, $queueName);

        /* @var $eventClass DomainEvent */
        foreach ($subscriber::subscribedTo() as $eventClass) {
            $queue->bind($exchangeName, $eventClass::messageName());
        }
    }

    private function declareExchange(string $exchangeName): void
    {
        $exchange = $this->connection->exchange($exchangeName);
        $exchange->setType(AMQP_EX_TYPE_TOPIC);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();
    }

    private function declareQueue(
        string $name,
        string $deadLetterExchange = null,
        string $deadLetterRoutingKey = null,
        int    $messageTtl = null
    ): \AMQPQueue
    {
        $queue = $this->connection->queue($name);

        if (null !== $deadLetterExchange) {
            $queue->setArgument('x-dead-letter-exchange', $deadLetterExchange);
        }

        if (null !== $deadLetterRoutingKey) {
            $queue->setArgument('x-dead-letter-routing-key', $deadLetterRoutingKey);
        }

        if (null !== $messageTtl) {
            $queue->setArgument('x-message-ttl', $messageTtl);
        }

        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();

        return $queue;
    }


}