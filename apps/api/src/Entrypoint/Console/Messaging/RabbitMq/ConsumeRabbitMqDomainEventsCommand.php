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
    public const NAME = 'library:rabbit:consume';

    public function __construct(
        private RabbitMqDomainEventsConsumer $consumer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Consume domain events from a RabbitMQ queue until the process is stopped')
            ->addArgument('queue', InputArgument::REQUIRED, 'Queue name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queueName = (string)$input->getArgument('queue');

        // consume() returns when the queue stays empty for the read timeout;
        // keep waiting. Supervisor stops the process with a signal, and a
        // message is only acknowledged after its subscribers ran.
        for (;;) {
            $this->consumer->consume($queueName);
        }
    }
}
