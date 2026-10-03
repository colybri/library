<?php

declare(strict_types=1);

namespace Application\Query\Edition\Get;

use Colybri\Library\Application\Query\Edition\Get\GetEditionQuery;
use Colybri\Library\Application\Query\Edition\Get\GetEditionQueryHandler;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetEditionQueryHandlerTest extends TestCase
{
    private MockObject $repository;

    private GetEditionQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EditionRepository::class);

        $this->handler = new GetEditionQueryHandler(
            new EditionFinder(
                $this->repository
            )
        );
    }

    private function query(Uuid $id): GetEditionQuery
    {
        return GetEditionQuery::fromPayload(
            Uuid::v4(),
            [
                GetEditionQuery::EDITION_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_edition_then_retrieve_it(): void
    {
        $editionId = Uuid::v4();
        $mother = new EditionObjectMother(id: $editionId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($mother->build());

        $edition = (array) json_decode(json_encode(($this->handler)($this->query($editionId))));

        $this->assertArrayHasKey('id', $edition);
        $this->assertArrayHasKey('year', $edition);
        $this->assertArrayHasKey('publisherId', $edition);
        $this->assertArrayHasKey('bookId', $edition);
        $this->assertArrayHasKey('googleId', $edition);
        $this->assertArrayHasKey('isbn', $edition);
        $this->assertArrayHasKey('title', $edition);
        $this->assertArrayHasKey('subtitle', $edition);
        $this->assertArrayHasKey('language', $edition);
        $this->assertArrayHasKey('city', $edition);
        $this->assertArrayHasKey('pages', $edition);
        $this->assertArrayHasKey('isOnLibrary', $edition);
        $this->assertArrayHasKey('condition', $edition);
    }

    /**
     * @test
     */
    public function given_not_existing_edition_then_throw_exception(): void
    {
        $this->expectException(EditionDoesNotExistException::class);

        $editionId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn(null);

        ($this->handler)($this->query($editionId));
    }
}
