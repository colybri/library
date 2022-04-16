<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Country\Get;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Country\Get\GetCountryQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class GetCountryQueryTest extends TestCase
{
    private $countryId;

    private $query;

    public function setUp(): void
    {
        $this->countryId = (Uuid::v4())->value();

        $this->query = GetCountryQuery::fromPayload(
            Uuid::v4(),
            [
                GetCountryQuery::COUNTRY_ID_PAYLOAD => $this->countryId,
            ]
        );
    }
    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(GetCountryQuery::class, $this->query);
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_open_async_standard_name(): void
    {
        self::assertMatchesRegularExpression('/^([a-z|_]+)\.([a-z|_]+)\.([0-9]){1,2}\.([a-z|_]+)\.([a-z|_]+)\.([a-z|_]+)$/', $this->query->messageName());
    }

    /**
     * @test
     */
    public function given_invalid_country_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetCountryQuery::fromPayload(
            Uuid::v4(),
            [
                GetCountryQuery::COUNTRY_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_country_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetCountryQuery::fromPayload(
            Uuid::v4(),
            [
                GetCountryQuery::COUNTRY_ID_PAYLOAD => null
            ]
        );
    }
}
