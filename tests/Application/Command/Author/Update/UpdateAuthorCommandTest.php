<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Update;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\Author\Update\UpdateAuthorCommand;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;

final class UpdateAuthorCommandTest extends TestCase
{
    private $authorId;
    private $name;
    private $countryId;
    private $isPseudonymOf;
    private $bornAt;
    private $deathAt;
    private $command;

    public function setUp(): void
    {
        $this->authorId = (Uuid::v4())->value();
        $this->name = 'Euripides';
        $this->countryId = (Uuid::v4())->value();
        $this->isPseudonymOf = null;
        $this->bornAt = -484;
        $this->deathAt = -406;

        $this->command = UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                UpdateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(UpdateAuthorCommand::class, $this->command);
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
    public function given_author_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->authorId)->equalTo($this->command->authorId()));
        self::assertTrue(AuthorName::from($this->name)->equalTo($this->command->name()));
        self::assertTrue(Uuid::from($this->countryId)->equalTo($this->command->countryId()));
        self::assertSame($this->isPseudonymOf, $this->command->isPseudonymOf());
        self::assertTrue(AuthorBornAt::from($this->bornAt)->equalTo($this->command->bornAt()));
        self::assertTrue(AuthorDeathAt::from($this->deathAt)->equalTo($this->command->deathAt()));
    }

    /**
     * @test
     */
    public function given_invalid_author_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_author_name_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => null,
                UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                UpdateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_country_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => '1984',
                UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                UpdateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_pseudonym_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => 'i-am-not-a-id',
                UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }
}
