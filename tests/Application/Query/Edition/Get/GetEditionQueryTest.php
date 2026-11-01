<?php

declare(strict_types=1);

namespace Application\Query\Edition\Get;

use Colybri\Library\Application\Query\Edition\Get\GetEditionQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class GetEditionQueryTest extends TestCase
{
    private $editionId;

    private $query;

    public function setUp(): void
    {
        $this->editionId = (Uuid::v4())->value();

        $this->query = GetEditionQuery::fromPayload(
            Uuid::v4(),
            [
                GetEditionQuery::EDITION_ID_PAYLOAD => $this->editionId,
            ]
        );
    }
    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(GetEditionQuery::class, $this->query);
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
        self::expectException(\InvalidArgumentException::class);

        GetEditionQuery::fromPayload(
            Uuid::v4(),
            [
                GetEditionQuery::EDITION_ID_PAYLOAD => "nosoyunaid",
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_author_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        GetEditionQuery::fromPayload(
            Uuid::v4(),
            [
                GetEditionQuery::EDITION_ID_PAYLOAD => null
            ]
        );
    }
}
