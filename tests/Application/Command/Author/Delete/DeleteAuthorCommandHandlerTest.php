<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Delete;

use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommand;
use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommandHandler;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Event\AuthorDeleted;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Colybri\Library\Domain\Service\Author\AuthorDeleter;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Colybri\Library\Tests\Mock\Infrastructure\Bus\FakeMessageBus;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteAuthorCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private FakeMessageBus $bus;

    private DeleteAuthorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->bus = new FakeMessageBus();
        $this->repository = $this->createMock(AuthorRepository::class);

        $this->handler = new DeleteAuthorCommandHandler(
            new AuthorDeleter(
                $this->repository,
                new AuthorFinder($this->repository),
            ),
            $this->bus,
        );
    }

    private function command(Uuid $id): DeleteAuthorCommand
    {
        return DeleteAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteAuthorCommand::AUTHOR_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_author_then_delete_it(): void
    {
        $authorId = Uuid::v4();
        $mother = new AuthorObjectMother(id: $authorId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($mother->build());

        $this->repository->expects($this->once())
            ->method('delete');

        ($this->handler)($this->command($authorId));

        $this->assertCount(1, $this->bus->events());
        $this->assertInstanceOf(AuthorDeleted::class, $this->bus->events()[0]);
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

        $this->repository->expects($this->never())->method('delete');

        ($this->handler)($this->command($authorId));
    }

}