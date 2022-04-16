<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Author\Get;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Author\Get\GetAuthorQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;

final class GetAuthorQueryTest extends TestCase
{
    private $authorId;

    private $query;

    public function setUp(): void
    {
        $this->authorId = (Uuid::v4())->value();

        $this->query = GetAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                GetAuthorQuery::AUTHOR_ID_PAYLOAD => $this->authorId,
            ]
        );
    }
    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(GetAuthorQuery::class, $this->query);
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
    public function given_invalid_author_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                GetAuthorQuery::AUTHOR_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_author_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        GetAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                GetAuthorQuery::AUTHOR_ID_PAYLOAD => null
            ]
        );
    }
}
