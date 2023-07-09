<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Messenger\Logging\Monolog\Processor;

use Colybri\Library\Infrastructure\Messenger\Logging\Monolog\Processor\InfoProcessor;
use Forkrefactor\Ddd\Domain\Model\ValueObject\DateTimeValueObject;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Forkrefactor\Ddd\Util\Message\AggregateMessage;
use Forkrefactor\Ddd\Util\Message\SimpleMessage;
use PHPUnit\Framework\TestCase;

class InfoProcessorTest extends TestCase
{
    /**
     * @test
     */
    public function given_context_without_message_key_then_return_same_record()
    {
        $record = [
            'context' => [],
        ];

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    /**
     * @test
     */
    public function given_context_without_domain_message_then_return_same_record()
    {
        $record = [
            'context' => [
                'message' => []
            ],
        ];

        $result = (new InfoProcessor())($record);

        $this->assertEquals($record, $result);
    }

    /**
     * @test
     */
    public function given_context_with_domain_message_then_return_record_with_message_info()
    {

        $uuid = Uuid::from('3c2c23f9-423d-4a87-ae42-4e05bb55926c');

        $simpleMessage = SimpleMessageFake::fromPayload($uuid, []);

        $record = [
            'context' => [
                'message' => $simpleMessage,
            ],
        ];

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('extra', $result);
        $this->assertArrayHasKey('message_id', $result['extra']);
        $this->assertEquals($uuid->value(), $result['extra']['message_id']);
        $this->assertArrayHasKey('name', $result['extra']);
        $this->assertEquals(SimpleMessageFake::messageName(), $result['extra']['name']);
        $this->assertArrayHasKey('type', $result['extra']);
        $this->assertEquals(SimpleMessageFake::messageType(), $result['extra']['type']);
        $this->assertArrayHasKey('payload', $result['extra']);
    }

    /**
     * @test
     */
    public function given_context_with_domain_aggregate_message_then_return_record_with_message_info()
    {
        $aggregateMessageUuid = Uuid::from('3c2c23f9-423d-4a87-ae42-4e05bb55926c');
        $occurredOn = new DateTimeValueObject('now');

        $aggregateMessage = AggregateMessageFake::fromPayload(
            Uuid::v4(),
            $aggregateMessageUuid,
            $occurredOn,
            [],
            1
        );

        $record = [
            'context' => [
                'message' => $aggregateMessage,
            ],
        ];

        $result = (new InfoProcessor())($record);

        $this->assertArrayHasKey('extra', $result);
        $this->assertArrayHasKey('aggregate_id', $result['extra']);
        $this->assertEquals($aggregateMessageUuid->value(), $result['extra']['aggregate_id']);
        $this->assertArrayHasKey('aggregate_version', $result['extra']);
        $this->assertEquals($aggregateMessage->aggregateVersion(), $result['extra']['aggregate_version']);
        $this->assertArrayHasKey('occurred_on', $result['extra']);
        $this->assertEquals($aggregateMessage->occurredOn()->format(\DateTime::ATOM), $result['extra']['occurred_on']);
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
