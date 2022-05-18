<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Edition\Create;

use Colybri\Library\Application\Command\Edition\Create\CreateEditionCommand;
use Colybri\Library\Application\Command\Edition\Create\CreateEditionCommandHandler;
use Colybri\Library\Domain\Model\Book\BookRepository;
use Colybri\Library\Domain\Model\Book\Exception\BookDoesNotExistException;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionAlreadyExistException;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Book\BookFinder;
use Colybri\Library\Domain\Service\Edition\EditionCreator;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Book\BookObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateEditionCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private MockObject $bookRepository;

    private MockObject $publisherRepository;

    private CreateEditionCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EditionRepository::class);
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->publisherRepository = $this->createMock(PublisherRepository::class);

        $this->handler = new CreateEditionCommandHandler(
            new EditionCreator(
                $this->repository,
                new PublisherFinder($this->publisherRepository),
                new BookFinder($this->bookRepository)
            )
        );
    }

    private function command(Uuid $id, Uuid $book, Uuid $publisher): CreateEditionCommand
    {
        return CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $id->value(),
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => 1985,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $publisher->value(),
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $book->value(),
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => null,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => '9788434407596',
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => 'Magia, ciencia y religión',
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => null,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => 'es',
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => null,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => null,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => 'second hand',
                CreateEditionCommand::EDITION_CITY_PAYLOAD => 'Madrrid',
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => null,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => true,
            ],
        );
    }

    /**
     * @test
     */
    public function given_non_existing_edition_then_create_it()
    {
        $this->repository->expects($this->once())->method('insert');

        $this->repository->expects($this->once())->method('find');

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

        ($this->handler)($this->command(Uuid::v4(), $bookId, $publisherId));
    }

    /**
     * @test
     */
    public function given_existing_edition_then_throw_exception()
    {
        $this->expectException(EditionAlreadyExistException::class);

        $this->repository->expects($this->never())->method('insert');

        $editionId = Uuid::v4();
        $edition = new EditionObjectMother(id: $editionId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($edition->build());

        ($this->handler)($this->command($editionId, Uuid::v4(), Uuid::v4()));
    }

    /**
     * @test
     */
    public function given_non_existing_publisher_then_throw_exception()
    {
        $this->expectException(PublisherDoesNotExistException::class);

        $this->repository->expects($this->never())->method('insert');

        $publisherId = Uuid::v4();

        $this->publisherRepository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn(null);

        ($this->handler)($this->command(Uuid::v4(), Uuid::v4(), $publisherId));
    }

    /**
     * @test
     */
    public function given_non_existing_book_then_throw_exception()
    {
        $this->expectException(BookDoesNotExistException::class);

        $this->repository->expects($this->never())->method('insert');

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

        ($this->handler)($this->command(Uuid::v4(), $bookId, $publisherId));
    }
}
