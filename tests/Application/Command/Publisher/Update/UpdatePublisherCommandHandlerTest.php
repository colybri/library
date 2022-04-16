<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Publisher\Update;

use Colybri\Library\Application\Command\Publisher\Update\UpdatePublisherCommand;
use Colybri\Library\Application\Command\Publisher\Update\UpdatePublisherCommandHandler;
use Colybri\Library\Domain\Model\Publisher\Exception\PublisherDoesNotExistException;
use Colybri\Library\Domain\Model\Publisher\PublisherRepository;
use Colybri\Library\Domain\Service\Publisher\PublisherFinder;
use Colybri\Library\Domain\Service\Publisher\PublisherUpdater;
use Colybri\Library\Tests\Mock\Domain\Model\Publisher\PublisherObjectMother;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Monolog\Test\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class UpdatePublisherCommandHandlerTest extends TestCase
{
    private MockObject $repository;

    private UpdatePublisherCommandHandler $handler;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PublisherRepository::class);

        $this->handler = new UpdatePublisherCommandHandler(
            new PublisherUpdater(
                $this->repository,
                new PublisherFinder(
                    $this->repository
                )
            )
        );
    }

    private function command(Uuid $id): UpdatePublisherCommand
    {
        return UpdatePublisherCommand::fromPayload(
            Uuid::v4(),
            [
                UpdatePublisherCommand::PUBLISHER_ID_PAYLOAD => $id->value(),
                UpdatePublisherCommand::PUBLISHER_NAME_PAYLOAD => 'Editorial Acantilado,',
                UpdatePublisherCommand::PUBLISHER_CITY_PAYLOAD => 'Barcelona',
                UpdatePublisherCommand::PUBLISHER_COUNTRY_ID_PAYLOAD => '34d41de4-d197-8f63-5014-7c5e5e8f71e3',
                UpdatePublisherCommand::PUBLISHER_FOUNDATION_PAYLOAD => 1999,
            ],
        );
    }

    /**
     * @test
     */
    public function given_existing_publisher_then_update_it()
    {
        $publisherId = Uuid::v4();
        $publisher = new PublisherObjectMother(id: $publisherId);

        $this->repository->expects($this->once())
            ->method('find')
            ->with($publisherId)
            ->willReturn($publisher->create());

        $this->repository->expects($this->once())->method('update');

        ($this->handler)($this->command($publisherId));
    }

    /**
     * @test
     */
    public function given_non_existing_publisher_then_throw_exception()
    {
        $this->expectException(PublisherDoesNotExistException::class);

        $this->repository->expects($this->never())->method('update');

        ($this->handler)($this->command(Uuid::v4()));
    }
}
