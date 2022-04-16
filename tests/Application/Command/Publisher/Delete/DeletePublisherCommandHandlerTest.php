<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Publisher\Delete;

use Colybri\Library\Application\Command\Publisher\Delete\DeletePublisherCommand;
use Colybri\Library\Application\Command\Publisher\Delete\DeletePublisherCommandHandler;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Publisher\PublisherDeleter;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeletePublisherCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private DeletePublisherCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PublisherRepository::class);

        $this->handler = new DeletePublisherCommandHandler(
            new PublisherDeleter(
                $this->repository,
                new PublisherFinder($this->repository),
            )
        );
    }

    private function command(Uuid $id): DeletePublisherCommand
    {
        return DeletePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                DeletePublisherCommand::PUBLISHER_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_publisher_then_delete_it(): void
    {
        $publisherId = Uuid::v4();
        $mother = new PublisherObjectMother(id: $publisherId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($mother->build());

        $this->repository->expects($this->once())
            ->method('delete');

        ($this->handler)($this->command($publisherId));
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

        $this->repository->expects($this->never())->method('delete');

        ($this->handler)($this->command($publisherId));
    }
}
