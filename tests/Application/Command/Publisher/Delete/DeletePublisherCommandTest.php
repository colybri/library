<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Publisher\Delete;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\Publisher\Delete\DeletePublisherCommand;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class DeletePublisherCommandTest extends TestCase
{
    private $publisherId;
    private $command;

    public function setUp(): void
    {
        $this->publisherId = (Uuid::v4())->value();

        $this->command = DeletePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                DeletePublisherCommand::PUBLISHER_ID_PAYLOAD => $this->publisherId,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(DeletePublisherCommand::class, $this->command);
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_open_async_standard_name(): void
    {
        self::assertMatchesRegularExpression('/^([a-z|_]+)\.([a-z|_]+)\.([0-9]){1,2}\.([a-z|_]+)\.([a-z|_]+)\.([a-z|_]+)$/', $this->command->messageName());
    }

    /**
     * @test
     */
    public function given_publisher_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->publisherId)->equalTo($this->command->publisherId()));
    }

    /**
     * @test
     */
    public function given_invalid_publisher_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        DeletePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                DeletePublisherCommand::PUBLISHER_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }
}
