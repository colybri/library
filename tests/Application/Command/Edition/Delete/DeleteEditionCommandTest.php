<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Edition\Delete;

use Colybri\Library\Application\Command\Edition\Delete\DeleteEditionCommand;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class DeleteEditionCommandTest extends TestCase
{
    private $editionId;
    private $command;

    public function setUp(): void
    {
        $this->editionId = (Uuid::v4())->value();

        $this->command = DeleteEditionCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteEditionCommand::EDITION_ID_PAYLOAD => $this->editionId,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(DeleteEditionCommand::class, $this->command);
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
    public function given_edition_id_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->editionId)->equalTo($this->command->editionId()));
    }

    /**
     * @test
     */
    public function given_invalid_edition_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        DeleteEditionCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteEditionCommand::EDITION_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }
}
