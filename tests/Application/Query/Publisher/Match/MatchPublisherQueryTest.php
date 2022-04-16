<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Publisher\Match;

use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Publisher\Macth\MatchPublisherQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class MatchPublisherQueryTest extends TestCase
{
    private $keywords;
    private $offset;
    private $limit;

    private $query;

    public function setUp(): void
    {
        $this->keywords = 'Papua';
        $this->offset = 0;
        $this->limit = 3;

        $this->query = MatchPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                MatchPublisherQuery::KEYWORDS_PAYLOAD => $this->keywords,
                MatchPublisherQuery::OFFSET_PAYLOAD => $this->offset,
                MatchPublisherQuery::LIMIT_PAYLOAD => $this->limit,

            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(MatchPublisherQuery::class, $this->query);
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
    public function given_null_country_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        MatchPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                MatchPublisherQuery::KEYWORDS_PAYLOAD => null
            ]
        );
    }
}
