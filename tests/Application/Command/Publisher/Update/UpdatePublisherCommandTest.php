<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Publisher\Update;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Command\Publisher\Update\UpdatePublisherCommand;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherCity;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherFoundationYear;
use Colybri\Library\Domain\Model\Publisher\ValueObject\PublisherName;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;

final class UpdatePublisherCommandTest extends TestCase
{
    private $publisherId;
    private $name;
    private $city;
    private $countryId;
    private $foundationYear;
    private $command;

    public function setUp(): void
    {
        $this->publisherId = (Uuid::v4())->value();
        $this->name = 'Plantin Press';
        $this->city = 'Antwerp';
        $this->countryId = "4e971bef-5e80-a79e-c2de-c7e807e5c28f";
        $this->foundationYear = 1555;


        $this->command = UpdatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD =>  $this->publisherId,
                UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => $this->name,
                UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $this->city,
                UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $this->foundationYear,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(UpdatePublisherCommand::class, $this->command);
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
        self::assertTrue(PublisherName::from($this->name)->equalTo($this->command->name()));
        self::assertTrue(PublisherCity::from($this->city)->equalTo($this->command->city()));
        self::assertTrue(Uuid::from($this->countryId)->equalTo($this->command->countryId()));
        self::assertTrue(PublisherFoundationYear::from($this->foundationYear)->equalTo($this->command->foundation()));
    }

    /**
     * @test
     */
    public function given_invalid_publisher_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD => "234aka_dae3",
                UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => $this->name,
                UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $this->city,
                UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $this->foundationYear,
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_publisher_name_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD => $this->publisherId,
                UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => null,
                UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => $this->countryId,
                UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $this->city,
                UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $this->foundationYear,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_country_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        UpdatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD => $this->publisherId,
                UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => $this->name,
                UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => '1984',
                UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => $this->city,
                UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => $this->foundationYear,
            ]
        );
    }
}
