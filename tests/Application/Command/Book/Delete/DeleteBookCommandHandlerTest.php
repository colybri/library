<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Book\Delete;

use Colybri\Library\Application\Command\Book\Delete\DeleteBookCommand;
use Colybri\Library\Application\Command\Book\Delete\DeleteBookCommandHandler;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Event\BookDeleted;
use Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException;
use Colybri\Library\Domain\Service\Book\BookDeleter;
use Colybri\Library\Domain\Service\Book\BookFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Book\BookObjectMother;
use Colybri\Library\Tests\Mock\Infrastructure\Bus\FakeMessageBus;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteBookCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private FakeMessageBus $bus;

    private DeleteBookCommandHandler $handler;

    protected function setUp(): void
    {
        $this->bus = new FakeMessageBus();
        $this->repository = $this->createMock(BookRepository::class);

        $this->handler = new DeleteBookCommandHandler(
            new BookDeleter(
                $this->repository,
                new BookFinder($this->repository),
            ),
            $this->bus
        );
    }

    private function command(Uuid $id): DeleteBookCommand
    {
        return DeleteBookCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteBookCommand::BOOK_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_book_then_delete_it(): void
    {
        $bookId = Uuid::v4();
        $mother = new BookObjectMother(id: $bookId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn($mother->build());

        $this->repository->expects($this->once())
            ->method('delete');

        ($this->handler)($this->command($bookId));

        $this->assertCount(1, $this->bus->events());
        $this->assertInstanceOf(BookDeleted::class, $this->bus->events()[0]);
    }

    /**
     * @test
     */
    public function given_not_existing_book_then_throw_exception(): void
    {
        $this->expectException(BookDoesNotExistException::class);

        $bookId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn(null);

        $this->repository->expects($this->never())->method('delete');

        ($this->handler)($this->command($bookId));
    }
}
