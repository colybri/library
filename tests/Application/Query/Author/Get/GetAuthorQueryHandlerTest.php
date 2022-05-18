<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Author\Get;

use Colybri\Library\Application\Query\Author\Get\GetAuthorQuery;
use Colybri\Library\Application\Query\Author\Get\GetAuthorQueryHandler;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetAuthorQueryHandlerTest extends TestCase
{
    private MockObject $repository;

    private GetAuthorQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepository::class);

        $this->handler = new GetAuthorQueryHandler(
            new AuthorFinder(
                $this->repository
            )
        );
    }

    private function query(Uuid $id): GetAuthorQuery
    {
        return GetAuthorQuery::fromPayload(
            Uuid::v4(),
            [
                GetAuthorQuery::AUTHOR_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_author_then_retrieve_it(): void
    {
        $authorId = Uuid::v4();
        $mother = new AuthorObjectMother(id: $authorId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($mother->build());

        $author = (array) json_decode(json_encode(($this->handler)($this->query($authorId))));

        $this->assertArrayHasKey('id', $author);
        $this->assertArrayHasKey('name', $author);
        $this->assertArrayHasKey('countryId', $author);
        $this->assertArrayHasKey('isPseudonymOf', $author);
        $this->assertArrayHasKey('bornAt', $author);
        $this->assertArrayHasKey('deathAt', $author);
    }

    /**
     * @test
     */
    public function given_not_existing_author_then_throw_exception(): void
    {
        $this->expectException(AuthorDoesNotExistException::class);

        $authorId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn(null);

        ($this->handler)($this->query($authorId));
    }
}
