<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Messaging\Event\Redis;

use Colybri\Library\Domain\Messaging\ProcessorRepository;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

/**
 * Remembers which subscriber already processed which message, so a message
 * that RabbitMQ delivers twice (a retry, a redelivery after a crash) is not
 * processed twice. One key per message and subscriber, kept for $ttlSeconds.
 */
final class RedisProcessorRepository implements ProcessorRepository
{
    private const KEY_PREFIX = 'library:processed';

    public function __construct(private readonly \Redis $redis, private readonly int $ttlSeconds)
    {
    }

    public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName): bool
    {
        return 0 === (int) $this->redis->exists($this->key($messageId, $subscriberClassName));
    }

    public function processSubscriber(Uuid $messageId, string $subscriberClassName): void
    {
        $this->redis->set($this->key($messageId, $subscriberClassName), '1', ['ex' => $this->ttlSeconds]);
    }

    private function key(Uuid $messageId, string $subscriberClassName): string
    {
        return sprintf('%s:%s:%s', self::KEY_PREFIX, $subscriberClassName, $messageId->value());
    }
}
