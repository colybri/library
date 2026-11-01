<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\User\Create;

use Colybri\Library\Domain\Service\User\UserCreator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CreateUserCommandHandler implements MessageHandlerInterface
{
    public function __construct(private UserCreator $creator)
    {
    }

    public function __invoke(CreateUserCommand $command): void
    {
        $this->creator->execute($command->userId(), $command->email(), $command->password(), $command->isActive());
    }
}