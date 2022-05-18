<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Book\Create;

use Colybri\Library\Application\Command\Book\Create\CreateBookCommand;
use Colybri\Library\Application\Command\Book\Create\CreateBookCommandHandler;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Event\BookCreated;
use Colybri\Library\Domain\Model\Book\Exception\BookAlreadyExistException;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Colybri\Library\Domain\Service\Book\BookCreator;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Book\BookObjectMother;
use Colybri\Library\Tests\Mock\Infrastructure\Bus\FakeMessageBus;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateBookCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private MockObject $authorRepository;

    private FakeMessageBus $bus;

    private CreateBookCommandHandler $handler;

    protected function setUp(): void
    {
        $this->bus = new FakeMessageBus();
        $this->repository = $this->createMock(BookRepository::class);
        $this->authorRepository = $this->createMock(AuthorRepository::class);

        $this->handler = new CreateBookCommandHandler(
            new BookCreator(
                $this->repository,
                new AuthorFinder($this->authorRepository)
            ),
            $this->bus
        );
    }

    private function command(Uuid $id, Uuid $author): CreateBookCommand
    {
        return CreateBookCommand::fromPayload(
            Uuid::v4(),
            [
                CreateBookCommand::BOOK_ID_PAYLOAD => $id->value(),
                CreateBookCommand::BOOK_TITLE_PAYLOAD => 'De visione Dei',
                CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => null,
                CreateBookCommand::BOOK_AUTHORS_PAYLOAD => [$author->value()],
                CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => 1453,
                CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => false,
                CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => false,
                CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => false,
            ],
        );
    }

    /**
     * @test
     */
    public function given_non_existing_book_then_create_it()
    {
        $this->repository->expects($this->once())->method('insert');

        $authorId = Uuid::v4();
        $author = new AuthorObjectMother(id: $authorId);

        $this->repository->expects($this->once())
            ->method('find');

        $this->authorRepository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($author->build());

        ($this->handler)($this->command(Uuid::v4(), $authorId));

        $events = $this->bus->events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(BookCreated::class, $events[0]);
    }

    /**
     * @test
     */
    public function given_existing_book_then_throw_exception(): void
    {
        $this->expectException(BookAlreadyExistException::class);

        $this->repository->expects($this->never())->method('insert');

        $authorId = Uuid::v4();
        $author = new AuthorObjectMother(id: $authorId);

        $this->authorRepository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($author->build());

        $bookId = Uuid::v4();
        $book = new BookObjectMother(id: $bookId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn($book->build());

        ($this->handler)($this->command($bookId, $authorId));
    }

    /**
     * @test
     */
    public function given_non_existing_author_then_throw_exception(): void
    {
        $this->expectException(AuthorDoesNotExistException::class);

        $authorId = Uuid::v4();

        $this->authorRepository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn(null);

        $this->repository->expects($this->never())->method('insert');

        ($this->handler)($this->command(Uuid::v4(), $authorId));
    }
}
