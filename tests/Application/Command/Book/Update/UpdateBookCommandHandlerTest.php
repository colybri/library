<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Book\Update;

use Colybri\Library\Application\Command\Book\Update\UpdateBookCommand;
use Colybri\Library\Application\Command\Book\Update\UpdateBookCommandHandler;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Event\BookCreated;
use Colybri\Library\Domain\Model\Book\Exception\BookAlreadyExistException;
use Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Colybri\Library\Domain\Service\Book\BookFinder;
use Colybri\Library\Domain\Service\Book\BookUpdater;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Book\BookObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateBookCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private MockObject $authorRepository;

    private UpdateBookCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BookRepository::class);
        $this->authorRepository = $this->createMock(AuthorRepository::class);

        $this->handler = new UpdateBookCommandHandler(
            new BookUpdater(
                $this->repository,
                new BookFinder($this->repository),
                new AuthorFinder($this->authorRepository)
            )
        );
    }

    private function command(Uuid $id, Uuid $author): UpdateBookCommand
    {
        return UpdateBookCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateBookCommand::BOOK_ID_PAYLOAD => $id->value(),
                UpdateBookCommand::BOOK_TITLE_PAYLOAD => 'De visione Dei',
                UpdateBookCommand::BOOK_SUBTITLE_PAYLOAD => null,
                UpdateBookCommand::BOOK_AUTHORS_PAYLOAD => [$author->value()],
                UpdateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => 1453,
                UpdateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => false,
                UpdateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => false,
                UpdateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => false,
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_book_then_update_it()
    {
        $this->repository->expects($this->once())->method('update');


        $bookId = Uuid::v4();
        $book = new BookObjectMother(id: $bookId);
        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn($book->create());

        $authorId = Uuid::v4();
        $author = new AuthorObjectMother(id: $authorId);
        $this->authorRepository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($author->create());

        ($this->handler)($this->command($bookId, $authorId));
    }

    /**
     * @test
     */
    public function given_non_existing_book_then_throw_exception(): void
    {
        $this->expectException(BookDoesNotExistException::class);

        $this->repository->expects($this->never())->method('update');

        $authorId = Uuid::v4();

        $bookId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn(null);

        ($this->handler)($this->command($bookId, $authorId));
    }

    /**
     * @test
     */
    public function given_non_existing_author_then_throw_exception(): void
    {
        $this->expectException(AuthorDoesNotExistException::class);

        $this->repository->expects($this->never())->method('update');

        $authorId = Uuid::v4();

        $this->authorRepository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn(null);

        $bookId = Uuid::v4();
        $book = new BookObjectMother(id: $bookId);
        $this->repository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn($book->create());

        ($this->handler)($this->command($bookId, $authorId));
    }
}
