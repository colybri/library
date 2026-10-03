<?php

declare(strict_types=1);

namespace Application\Command\Edition\Update;

use Colybri\Library\Application\Command\Edition\Update\UpdateEditionCommand;
use Colybri\Library\Application\Command\Edition\Update\UpdateEditionHandler;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Book\BookFinder;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use Colybri\Library\Domain\Service\Edition\EditionUpdater;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Book\BookObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateEditionCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private MockObject $bookRepository;

    private MockObject $publisherRepository;

    private UpdateEditionHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EditionRepository::class);
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->publisherRepository = $this->createMock(PublisherRepository::class);

        $this->handler = new UpdateEditionHandler(
            new EditionUpdater(
                $this->repository,
                new EditionFinder($this->repository),
                new PublisherFinder($this->publisherRepository),
                new BookFinder($this->bookRepository)
            )
        );
    }

    private function command(Uuid $id, Uuid $book, Uuid $publisher): UpdateEditionCommand
    {
        return UpdateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateEditionCommand::EDITION_ID_PAYLOAD => $id->value(),
                UpdateEditionCommand::EDITION_YEAR_PAYLOAD => 1985,
                UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $publisher->value(),
                UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $book->value(),
                UpdateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => null,
                UpdateEditionCommand::EDITION_ISBN_PAYLOAD => '9788434407596',
                UpdateEditionCommand::EDITION_TITLE_PAYLOAD => 'Magia, ciencia y religión',
                UpdateEditionCommand::EDITION_SUBTITLE_PAYLOAD => null,
                UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD => 'es',
                UpdateEditionCommand::EDITION_IMAGE_PAYLOAD => null,
                UpdateEditionCommand::EDITION_CITY_PAYLOAD => 'Madrrid',
                UpdateEditionCommand::EDITION_PAGES_PAYLOAD => null,
                UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD => true,
                UpdateEditionCommand::EDITION_CONDITION_PAYLOAD => 'second hand',
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_edition_then_update_it()
    {
        $editionId = Uuid::v4();
        $edition = new EditionObjectMother(id: $editionId);
        $this->repository->expects($this->once())->method('update');

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($edition->build());

        $publisherId = Uuid::v4();
        $publisher = new PublisherObjectMother(id: $publisherId);

        $this->publisherRepository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($publisher->build());

        $bookId = Uuid::v4();
        $book = new BookObjectMother(id: $bookId);

        $this->bookRepository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn($book->build());

        ($this->handler)($this->command($editionId, $bookId, $publisherId));
    }

    /**
     * @test
     */
    public function given_not_existing_edition_then_throw_exception()
    {
        $this->expectException(EditionDoesNotExistException::class);

        $this->repository->expects($this->never())->method('update');

        $editionId = Uuid::v4();

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn(null);

        ($this->handler)($this->command($editionId, Uuid::v4(), Uuid::v4()));
    }

    /**
     * @test
     */
    public function given_non_existing_publisher_then_throw_exception()
    {
        $this->expectException(PublisherDoesNotExistException::class);

        $editionId = Uuid::v4();
        $edition = new EditionObjectMother(id: $editionId);
        $this->repository->expects($this->never())->method('update');

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($edition->build());

        $publisherId = Uuid::v4();

        $this->publisherRepository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn(null);

        ($this->handler)($this->command($editionId, Uuid::v4(), $publisherId));
    }

    /**
     * @test
     */
    public function given_non_existing_book_then_throw_exception()
    {
        $this->expectException(BookDoesNotExistException::class);

        $editionId = Uuid::v4();
        $edition = new EditionObjectMother(id: $editionId);
        $this->repository->expects($this->never())->method('update');

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($edition->build());

        $publisherId = Uuid::v4();
        $publisher = new PublisherObjectMother(id: $publisherId);

        $this->publisherRepository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($publisher->build());

        $bookId = Uuid::v4();
        $this->bookRepository->expects($this->once())
            ->method('find')
            ->with($bookId)
            ->willReturn(null);

        ($this->handler)($this->command($editionId, $bookId, $publisherId));
    }
}