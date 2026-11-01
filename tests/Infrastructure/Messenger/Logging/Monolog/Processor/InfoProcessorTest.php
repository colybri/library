<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Messenger\Logging\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use Colybri\Library\Infrastructure\Logging\Monolog\Processor\InfoProcessor;
use PcComponentes\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PcComponentes\Ddd\Util\Message\AggregateMessage;
use PcComponentes\Ddd\Util\Message\SimpleMessage;
use PcComponentes\Ddd\Util\Message\ValueObject\AggregateId;
use PHPUnit\Framework\TestCase;

class InfoProcessorTest extends TestCase
{
    private static function record(array $context): LogRecord
    {
        return new LogRecord(new \DateTimeImmutable(), 'test', Level::Info, 'message', $context);
    }

    #[Test]
    public function given_context_without_message_key_then_return_same_record()
    {
        $record = self::record([]);

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    #[Test]
    public function given_context_without_domain_message_then_return_same_record()
    {
        $record = self::record([
                'message' => []
            ]);

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    #[Test]
    public function given_context_with_domain_message_then_return_record_with_message_info()
    {

        $uuid = Uuid::from('3c2c23f9-423d-4a87-ae42-4e05bb55926c');

        $simpleMessage = SimpleMessageFake::fromPayload($uuid, []);

        $record = self::record([
                'message' => $simpleMessage,
            ]);

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('message_id', $result->extra);
        $this->assertEquals($uuid->value(), $result->extra['message_id']);
        $this->assertArrayHasKey('name', $result->extra);
        $this->assertEquals(SimpleMessageFake::messageName(), $result->extra['name']);
        $this->assertArrayHasKey('type', $result->extra);
        $this->assertEquals(SimpleMessageFake::messageType(), $result->extra['type']);
        $this->assertArrayHasKey('payload', $result->extra);
    }

    #[Test]
    public function given_context_with_domain_aggregate_message_then_return_record_with_message_info()
    {
        $aggregateMessageUuid = Uuid::from('3c2c23f9-423d-4a87-ae42-4e05bb55926c');
        $occurredOn = DateTimeValueObject::now();

        $aggregateMessage = AggregateMessageFake::fromPayload(
            Uuid::v4(),
            AggregateId::from($aggregateMessageUuid->value()),
            $occurredOn,
            [],
            1
        );

        $record = self::record([
                'message' => $aggregateMessage,
            ]);

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('aggregate_id', $result->extra);
        $this->assertEquals($aggregateMessageUuid->value(), $result->extra['aggregate_id']);
        $this->assertArrayHasKey('aggregate_version', $result->extra);
        $this->assertEquals($aggregateMessage->aggregateVersion(), $result->extra['aggregate_version']);
        $this->assertArrayHasKey('occurred_on', $result->extra);
        $this->assertEquals($aggregateMessage->occurredOn()->format(\DateTime::ATOM), $result->extra['occurred_on']);
    }
}

class SimpleMessageFake extends SimpleMessage
{
    public static function messageName(): string
    {
        return 'message_name';
    }

    public static function messageVersion(): string
    {
        return 'message_version';
    }

    public static function messageType(): string
    {
        return 'message_type';
    }

    protected function assertPayload(): void
    {
    }
}

class AggregateMessageFake extends AggregateMessage
{
    public static function messageName(): string
    {
        return 'aggregate_message_name';
    }

    public static function messageVersion(): string
    {
        return 'aggregate_message_version';
    }

    public static function messageType(): string
    {
        return 'aggregate_message_type';
    }

    protected function assertPayload(): void
    {
    }
}
