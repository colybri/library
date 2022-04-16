<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Publisher\Get;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Publisher\Get\GetPublisherQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class GetPublisherQueryTest extends TestCase
{
    private $publisherId;

    private $query;

    public function setUp(): void
    {
        $this->publisherId = (Uuid::v4())->value();

        $this->query = GetPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                GetPublisherQuery::PUBLISHER_ID_PAYLOAD => $this->publisherId,
            ]
        );
    }
    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(GetPublisherQuery::class, $this->query);
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
    public function given_invalid_publisher_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                GetPublisherQuery::PUBLISHER_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_publisher_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                GetPublisherQuery::PUBLISHER_ID_PAYLOAD => null
            ]
        );
    }
}
