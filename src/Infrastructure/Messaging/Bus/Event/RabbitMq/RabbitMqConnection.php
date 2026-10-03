<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
class RabbitMqConnection
{
    private static $connection;
    private static  $channel;
    /** @var AMQPExchange[] */
    private static $exchanges = [];
    /** @var AMQPQueue[] */
    private static $queues = [];

    public function __construct(private readonly array $configuration)
    {
    }

    public function queue(string $name): AMQPQueue
    {
        if (!array_key_exists($name, self::$queues)) {
            $queue = new AMQPQueue($this->channel());
            $queue->setName($name);

            self::$queues[$name] = $queue;
        }

        return self::$queues[$name];
    }

    public function exchange(string $name): AMQPExchange
    {
        if (!array_key_exists($name, self::$exchanges)) {
            $exchange = new AMQPExchange($this->channel());
            $exchange->setName($name);

            self::$exchanges[$name] = $exchange;
        }

        return self::$exchanges[$name];
    }

    private function channel(): AMQPChannel
    {
        return self::$channel = self::$channel && self::$channel->isConnected()
            ? self::$channel
            : new AMQPChannel($this->connection());
    }

    private function connection(): AMQPConnection
    {
        self::$connection = self::$connection ?: new AMQPConnection($this->configuration);

        if (!self::$connection->isConnected()) {
            self::$connection->pconnect();
        }

        return self::$connection;
    }
}