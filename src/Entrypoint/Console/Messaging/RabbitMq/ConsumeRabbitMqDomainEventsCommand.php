<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Messaging\RabbitMq;

use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitMqDomainEventsConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsumeRabbitMqDomainEventsCommand extends Command
{
    private const NAME = 'library:rabbit:consume';

    public function __construct(
        private RabbitMqDomainEventsConsumer $consumer
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Consume domain events from the RabbitMQ')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = (string)$input->getArgument('queue');
        $this->consumer->consume($queueName);

        return Command::SUCCESS;
    }

}