<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\User;

use Colybri\Library\Application\Command\User\Create\CreateUserCommand;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class UserCreateCommand extends Command
{
    private const NAME = 'library:user:create';
    public function __construct(private MessageBusInterface $commandBus)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Create a new user');

        $this->addArgument('id', InputArgument::REQUIRED, 'User id');
        $this->addArgument('email', InputArgument::REQUIRED, 'Email');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password');
        $this->addArgument('isActive', InputArgument::REQUIRED, 'If is an active user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = $input->getArgument('id');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $isActive = $input->getArgument('isActive');

        $this->commandBus->dispatch(CreateUserCommand::fromPayload(
            Uuid::v4(),
            [
                CreateUserCommand::USER_ID_PAYLOAD => $userId,
                CreateUserCommand::USER_EMAIL_PAYLOAD => $email,
                CreateUserCommand::USER_PASSWORD_PAYLOAD => $password,
                CreateUserCommand::USER_IS_ACTIVE_PAYLOAD => (bool) $isActive
            ]
        ));

        $output->writeln("<info>User: {$email} created.</info>");

        return Command::SUCCESS;
    }
}