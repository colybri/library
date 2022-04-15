<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Author\Update;

use Colybri\Library\Application\Command\Author\Update\UpdateAuthorCommand;
use Colybri\Library\Application\Command\Author\Update\UpdateAuthorCommandHandler;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\Exception\AuthorDoesNotExistException;
use Colybri\Library\Domain\Service\Author\AuthorFinder;
use Colybri\Library\Domain\Service\Author\AuthorUpdater;
use Colybri\Library\Tests\Mock\Domain\Model\Author\AuthorObjectMother;
use Colybri\Library\Tests\Mock\Infrastructure\Bus\FakeMessageBus;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UpdateAuthorCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private UpdateAuthorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AuthorRepository::class);

        $this->handler = new UpdateAuthorCommandHandler(
            new AuthorUpdater(
                $this->repository,
                new AuthorFinder(
                    $this->repository
                )
            )
        );
    }

    private function command(Uuid $id): UpdateAuthorCommand
    {
        return UpdateAuthorCommand::fromPayload(
            Uuid::v4(),
            [
                UpdateAuthorCommand::AUTHOR_ID_PAYLOAD => $id->value(),
                UpdateAuthorCommand::AUTHOR_NAME_PAYLOAD => 'Jean-Pierre-Louis de Luchet,',
                UpdateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => 'c310c88a-c751-167e-006d-8fb1cc36e136',
                UpdateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => null,
                UpdateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => 1740,
                UpdateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => 1792
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_author_then_update_it()
    {
        $authorId = Uuid::v4();
        $author = new AuthorObjectMother(id: $authorId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($authorId)
            ->willReturn($author->create());

        $this->repository->expects($this->once())->method('update');

        ($this->handler)($this->command($authorId));
    }

    /**
     * @test
     */
    public function given_non_existing_author_then_throw_exception()
    {
        $this->expectException(AuthorDoesNotExistException::class);

        $this->repository->expects($this->never())->method('update');

        ($this->handler)($this->command(Uuid::v4()));
    }
}
