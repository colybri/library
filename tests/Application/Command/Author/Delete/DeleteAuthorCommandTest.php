<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Delete;

use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommand;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;
use Assert\InvalidArgumentException;

final class DeleteAuthorCommandTest extends TestCase
{
    private $authorId;
    private $command;

    public function setUp(): void
    {
        $this->authorId = (Uuid::v4())->value();

        $this->command = DeleteAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(DeleteAuthorCommand::class, $this->command);
    }

    /**
     * @test
     */
    public function given_author_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->authorId)->equalTo($this->command->authorId()));
    }

    /**
     * @test
     */
    public function given_invalid_author_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        DeleteAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteAuthorCommand::AUTHOR_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }
}
