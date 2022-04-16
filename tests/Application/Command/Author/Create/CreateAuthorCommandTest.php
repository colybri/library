<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Create;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class CreateAuthorCommandTest extends TestCase
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

        $this->command = CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }


    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(CreateAuthorCommand::class, $this->command);
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
        self::assertEquals($this->isPseudonymOf, $this->command->isPseudonymOf());
        self::assertTrue(AuthorBornAt::from($this->bornAt)->equalTo($this->command->bornAt()));
        self::assertTrue(AuthorDeathAt::from($this->deathAt)->equalTo($this->command->deathAt()));
    }

    /**
     * @test
     */
    public function given_invalid_author_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => "234aka_dae3",
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_author_name_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => null,
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_country_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => '1984',
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_pseudonym_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => $this->name,
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => 'i-am-not-a-id',
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => $this->deathAt,
            ]
        );
    }
}
