<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Edition\Delete;

use Colybri\Library\Application\Command\Edition\Delete\DeleteEditionCommand;
use Colybri\Library\Application\Command\Edition\Delete\DeleteEditionCommandHandler;
use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException;
use Colybri\Library\Domain\Service\Edition\EditionDeleter;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteEditionCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private DeleteEditionCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EditionRepository::class);

        $this->handler = new DeleteEditionCommandHandler(
            new EditionDeleter(
                $this->repository,
                new EditionFinder($this->repository),
            )
        );
    }

    private function command(Uuid $id): DeleteEditionCommand
    {
        return DeleteEditionCommand::fromPayload(
            Uuid::v4(),
            [
                DeleteEditionCommand::EDITION_ID_PAYLOAD => $id->value(),
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_edition_then_delete_it(): void
    {
        $editionId = Uuid::v4();
        $mother = new EditionObjectMother(id: $editionId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($editionId)
            ->willReturn($mother->build());

        $this->repository->expects($this->once())
            ->method('delete');

        ($this->handler)($this->command($editionId));
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

        $this->repository->expects($this->never())->method('delete');

        ($this->handler)($this->command($editionId));
    }
}
