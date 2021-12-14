<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorDeathAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

class CreateAuthorCommandTest extends TestCase
{

    private $authorId;
    private $firstName;
    private $lastName;
    private $countryId;
    private $isPseudonymOf;
    private $bornAt;
    private $deathAt;
    private $command;

    public function setUp(): void
    {
         $this->authorId = (Uuid::v4())->value();
         $this->firstName = 'Euripides';
         $this->lastName = null;
         $this->countryId = (Uuid::v4())->value();
         $this->isPseudonymOf = null;
         $this->bornAt = -484;
         $this->deathAt = -406;

        $this->command = CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::FIRST_NAME_PAYLOAD => $this->firstName,
                CreateAuthorCommand::LAST_NAME_PAYLOAD => $this->lastName,
                CreateAuthorCommand::COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::BORN_AT_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::DEATH_AT_PAYLOAD => $this->deathAt,
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
    public function given_author_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->authorId)->equalTo($this->command->authorId()));
        self::assertTrue(AuthorFirstName::from($this->firstName)->equalTo($this->command->firstName()));
        self::assertSame($this->lastName, $this->command->lastName());
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

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::ID_PAYLOAD => "234aka_dae3",
                CreateAuthorCommand::FIRST_NAME_PAYLOAD => $this->firstName,
                CreateAuthorCommand::LAST_NAME_PAYLOAD => $this->lastName,
                CreateAuthorCommand::COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::BORN_AT_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::DEATH_AT_PAYLOAD => $this->deathAt,
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_author_first_name_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::FIRST_NAME_PAYLOAD => null,
                CreateAuthorCommand::LAST_NAME_PAYLOAD => $this->lastName,
                CreateAuthorCommand::COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::BORN_AT_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::DEATH_AT_PAYLOAD => $this->deathAt,
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
                CreateAuthorCommand::ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::FIRST_NAME_PAYLOAD => $this->firstName,
                CreateAuthorCommand::LAST_NAME_PAYLOAD => $this->lastName,
                CreateAuthorCommand::COUNTRY_ID_PAYLOAD => '1984',
                CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => $this->isPseudonymOf,
                CreateAuthorCommand::BORN_AT_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::DEATH_AT_PAYLOAD => $this->deathAt,
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
                CreateAuthorCommand::ID_PAYLOAD => $this->authorId,
                CreateAuthorCommand::FIRST_NAME_PAYLOAD => $this->firstName,
                CreateAuthorCommand::LAST_NAME_PAYLOAD => $this->lastName,
                CreateAuthorCommand::COUNTRY_ID_PAYLOAD => $this->countryId,
                CreateAuthorCommand::IS_PSEUDONYM_OF_PAYLOAD => 'no-soy-una-id',
                CreateAuthorCommand::BORN_AT_PAYLOAD => $this->bornAt,
                CreateAuthorCommand::DEATH_AT_PAYLOAD => $this->deathAt,
            ]
        );
    }
}