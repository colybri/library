<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Edition\Seek;

use Colybri\Library\Application\Query\Edition\Seek\SeekEditionQuery;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class SeekEditionQueryTest extends TestCase
{
    private $title;
    private $author;
    private $isbn;
    private $publisher;

    private $query;

    public function setUp(): void
    {
        $this->title = 'De camino a Babadag';
        $this->author = 'stasiuk';
        $this->isbn = '978-84-96834-34-7';
        $this->publisher = 'Acantilado';

        $this->query = SeekEditionQuery::fromPayload(
            Uuid::v4(),
            [
                SeekEditionQuery::EDITION_TITLE_PAYLOAD => $this->title,
                SeekEditionQuery::EDITION_AUTHOR_PAYLOAD => $this->author,
                SeekEditionQuery::EDITION_ISBN_PAYLOAD => $this->isbn,
                SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD => $this->publisher,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(SeekEditionQuery::class, $this->query);
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
    public function given_all_parameters_null_when_query_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        SeekEditionQuery::fromPayload(
            Uuid::v4(),
            [
                SeekEditionQuery::EDITION_TITLE_PAYLOAD => null,
                SeekEditionQuery::EDITION_AUTHOR_PAYLOAD => null,
                SeekEditionQuery::EDITION_ISBN_PAYLOAD => null,
                SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD => null
            ]
        );
    }
}
