<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Query\Publisher\Get;

use Colybri\Library\Application\Query\Publisher\Get\GetPublisherQuery;
use Colybri\Library\Application\Query\Publisher\Get\GetPublisherQueryHandler;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetPublisherQueryHandlerTest extends TestCase
{
    private MockObject $repository;

    private GetPublisherQueryHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PublisherRepository::class);

        $this->handler = new GetPublisherQueryHandler(
            new PublisherFinder(
                $this->repository
            )
        );
    }

    private function query(Uuid $id): GetPublisherQuery
    {
        return GetPublisherQuery::fromPayload(
            Uuid::v4(),
            [
                GetPublisherQuery::PUBLISHER_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_publisher_then_retrieve_it(): void
    {
        $publisherId = Uuid::v4();
        $mother = new PublisherObjectMother(id: $publisherId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($mother->build());

        ($this->handler)($this->query($publisherId));
    }

    /**
     * @test
     */
    public function given_not_existing_publisher_then_throw_exception(): void
    {
        $this->expectException(PublisherDoesNotExistException::class);

        $publisherId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn(null);

        ($this->handler)($this->query($publisherId));
    }
}
