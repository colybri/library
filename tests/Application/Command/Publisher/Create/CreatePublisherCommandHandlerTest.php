<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Publisher\Create;

use Colybri\Library\Application\Command\Publisher\Create\CreatePublisherCommand;
use Colybri\Library\Application\Command\Publisher\Create\CreatePublisherCommandHandler;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherAlreadyExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Publisher\PublisherCreator;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreatePublisherCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private CreatePublisherCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PublisherRepository::class);

        $this->handler = new CreatePublisherCommandHandler(
            new PublisherCreator(
                $this->repository
            )
        );
    }

    private function command(Uuid $id): CreatePublisherCommand
    {
        return CreatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                CreatePublisherCommand::PUBLISHER_ID_PAYLOAD => $id->value(),
                CreatePublisherCommand::PUBLISHER_NAME_PAYLOAD => 'Editorial Acantilado',
                CreatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => '34d41de4-d197-8f63-5014-7c5e5e8f71e3',
                CreatePublisherCommand::PUBLISHER_CITY_PAYLOAD => 'Barcelona',
                CreatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => 1999,
            ],
        );
    }

    /**
     * @test
     */
    public function given_non_existing_publisher_then_create_it()
    {
        $this->repository->expects($this->once())->method('insert');

        ($this->handler)($this->command(Uuid::v4()));
    }

    /**
     * @test
     */
    public function given_existing_publisher_then_throw_exception()
    {
        $this->expectException(PublisherAlreadyExistException::class);

        $publisherId = Uuid::v4();
        $publisher = new PublisherObjectMother(id: $publisherId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($publisher->create());

        $this->repository->expects($this->never())->method('insert');

        ($this->handler)($this->command($publisherId));
    }
}
