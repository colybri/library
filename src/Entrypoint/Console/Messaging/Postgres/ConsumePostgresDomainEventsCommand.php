<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Messaging\Postgres;

use Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres\PostgresDomainEventsConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumePostgresDomainEventsCommand extends Command
{
    private const NAME = 'library:postgres:consume';

    public function __construct(
        private PostgresDomainEventsConsumer $consumer,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Consume domain events from Postgres')
            ->addArgument('quantity', InputArgument::REQUIRED, 'Quantity of events to process');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventsToProcess = (int)$input->getArgument('quantity');

        $this->consumer->consume($eventsToProcess);

        return Command::SUCCESS;
    }
}