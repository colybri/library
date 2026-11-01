<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Author\Match;

use PHPUnit\Framework\Attributes\Test;
use Assert\InvalidArgumentException;
use Colybri\Library\Application\Query\Author\Match\MatchAuthorQuery;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class MatchAuthorQueryTest extends TestCase
{
    private $keywords;
    private $offset;
    private $limit;

    private $query;

    public function setUp(): void
    {
        $this->keywords = 'barruel';
        $this->offset = 0;
        $this->limit = 3;

        $this->query = MatchAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                MatchAuthorQuery::KEYWORDS_PAYLOAD => $this->keywords,
                MatchAuthorQuery::OFFSET_PAYLOAD => $this->offset,
                MatchAuthorQuery::LIMIT_PAYLOAD => $this->limit,

            ]
        );
    }

    #[Test]
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(MatchAuthorQuery::class, $this->query);
    }

    #[Test]
    public function given_subject_under_test_when_set_up_then_should_return_open_async_standard_name(): void
    {
        self::assertMatchesRegularExpression('/^([a-z|_]+)\.([a-z|_]+)\.([0-9]){1,2}\.([a-z|_]+)\.([a-z|_]+)\.([a-z|_]+)$/', $this->query->messageName());
    }

    #[Test]
    public function given_null_author_id_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(InvalidArgumentException::class);

        MatchAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                MatchAuthorQuery::KEYWORDS_PAYLOAD => null
            ]
        );
    }
}
