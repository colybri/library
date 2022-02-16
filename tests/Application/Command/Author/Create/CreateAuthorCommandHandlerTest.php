<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Create;

use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommandHandler;
use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommand;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;
use Colybri\Library\Domain\Model\Author\Exception\AuthorAlreadyExistException;
use Colybri\Library\Domain\Service\Author\AuthorCreator;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Colybri\Library\Tests\Mock\Infrastructure\Bus\FakeMessageBus;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateAuthorCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private FakeMessageBus $bus;

    private CreateAuthorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->bus = new FakeMessageBus();
        $this->repository = $this->createMock(AuthorRepository::class);

        $this->handler = new CreateAuthorCommandHandler(
            new AuthorCreator(
                $this->repository
            ),
            $this->bus,
        );
    }

    private function command(Uuid $id): CreateAuthorCommand
    {
        return CreateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $id->value(),
                CreateAuthorCommand::AUTHOR_NAME_PAYLOAD => 'Agustín de Hipona',
                CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => 'd4fff356-ad67-9347-f167-f904adcd8953',
                CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => null,
                CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => 396,
                CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => 430
            ],
        );
    }

    /**
     * @test
     */
    public function given_non_existing_author_then_create_it()
    {
        $this->repository->expects($this->once())->method('insert');

        ($this->handler)($this->command(Uuid::v4()));

        $events = $this->bus->events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AuthorCreated::class, $events[0]);
    }

    /**
     * @test
     */
    public function given_existing_author_then_throw_exception()
    {
        $this->expectException(AuthorAlreadyExistException::class);

        $authorId = Uuid::v4();
        $author = new AuthorObjectMother(id: $authorId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($author->create());

        $this->repository->expects($this->never())->method('insert');

        ($this->handler)($this->command($authorId));
    }
}