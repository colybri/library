<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Book\Delete;

use Colybri\Library\Application\Command\Book\Delete\DeleteBookCommand;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class DeleteBookCommandTest extends TestCase
{
    private $bookId;
    private $command;

    public function setUp(): void
    {
        $this->bookId = (Uuid::v4())->value();

        $this->command = DeleteBookCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteBookCommand::BOOK_ID_PAYLOAD => $this->bookId,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(DeleteBookCommand::class, $this->command);
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
    public function given_book_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->bookId)->equalTo($this->command->bookId()));
    }

    /**
     * @test
     */
    public function given_invalid_book_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        DeleteBookCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteBookCommand::BOOK_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }
}
