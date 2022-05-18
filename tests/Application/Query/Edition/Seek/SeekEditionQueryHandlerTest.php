<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Edition\Seek;

use Colybri\Library\Application\Query\Edition\Seek\SeekEditionQuery;
use Colybri\Library\Application\Query\Edition\Seek\SeekEditionQueryHandler;
use Colybri\Library\Domain\Model\Edition\Edition;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SeekEditionQueryHandlerTest extends TestCase
{
    private SeekEditionQueryHandler $handler;
    private MockObject $editionRepository;

    protected function setUp(): void
    {
        $this->editionRepository = $this->createMock(EditionRepository::class);
        $this->handler = new SeekEditionQueryHandler(
            $this->editionRepository
        );
    }

    private function query(): SeekEditionQuery
    {
        $query = SeekEditionQuery::fromPayload(
            Uuid::from('2e9d7383-775f-4786-a7f3-b9c560a161ba'),
            [
                SeekEditionQuery::EDITION_AUTHOR_PAYLOAD => 'Autor',
                SeekEditionQuery::EDITION_ISBN_PAYLOAD => '234820348',
                SeekEditionQuery::EDITION_PUBLISHER_PAYLOAD => 'Publicador',
                SeekEditionQuery::EDITION_TITLE_PAYLOAD => 'Algún título',
            ],
        );
        return $query;
    }

    private function editions(): array
    {
        $mockOne = new EditionObjectMother();
        $mockTwo = new EditionObjectMother();
        return [$mockOne->build(), $mockTwo->build()];
    }

    /**
     * @test
     */
    public function given_search_parameters_when_query_is_invoke_then_retrieve_result(): void
    {

        list($mockOne, $mockTwo) = $this->editions();

        $this->editionRepository->expects(static::once())
            ->method('match')
            ->willReturn([$mockOne, $mockTwo]);

        $response = ($this->handler)($this->query());

        self::assertInstanceOf(Edition::class, $response[0]);

        $edition = (array) json_decode(json_encode($response[0]));

        $this->assertArrayHasKey('id', $edition);
        $this->assertArrayHasKey('title', $edition);
        $this->assertArrayHasKey('subtitle', $edition);
        $this->assertArrayHasKey('year', $edition);
        $this->assertArrayHasKey('pages', $edition);
        $this->assertArrayHasKey('language', $edition);
        $this->assertArrayHasKey('googleId', $edition);
        $this->assertArrayHasKey('isbn', $edition);
        $this->assertArrayHasKey('city', $edition);
        $this->assertArrayHasKey('publisherId', $edition);
        $this->assertArrayHasKey('bookId', $edition);
        $this->assertArrayHasKey('isOnLibrary', $edition);
        $this->assertArrayHasKey('condition', $edition);
    }
}
