<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Messaging\RabbitMq;

use Colybri\Library\Domain\Messaging\DomainEventSubscriber;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Queue\RabbitMqQueueNameFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateSupervisorRabbitMqConsumerFilesCommand extends Command
{
    private const EVENTS_TO_PROCESS_AT_TIME = 200;
    private const NUMBERS_OF_PROCESSES_PER_SUBSCRIBER = 1;
    private const SUPERVISOR_PATH = '/srv/app/build/supervisord';

    protected const NAME = 'libary:rabbitmq:generate-supervisor';

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate the supervisor configuration for every RabbitMQ subscriber')
            ->addArgument('command-path', InputArgument::OPTIONAL, 'Path on this is gonna be deployed', '/srv/app');
    }

    public function __construct(private iterable $subscribers)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = (string)$input->getArgument('command-path');

        foreach ($this->subscribers as $subscriber) {

            $this->configCreator($path, $subscriber);
        }

        return Command::SUCCESS;
    }

    private function configCreator(string $path, DomainEventSubscriber $subscriber): void
    {
        $queueName = RabbitMqQueueNameFormatter::format($subscriber);
        $subscriberName = RabbitMqQueueNameFormatter::shortFormat($subscriber);

        $fileContent = str_replace(
            [
                '{subscriber_name}',
                '{queue_name}',
                '{path}',
                '{processes}',
                '{events_to_process}',
            ],
            [
                $subscriberName,
                $queueName,
                $path,
                self::NUMBERS_OF_PROCESSES_PER_SUBSCRIBER,
                self::EVENTS_TO_PROCESS_AT_TIME
            ],
            $this->template()
        );

        file_put_contents($this->fileName($subscriberName), $fileContent);
    }

    private function template(): string
    {
        return <<<EOF
            [program:library_{queue_name}]
            command      = {path}/bin/console library:rabbitmq:consume --env=prod {queue_name}
            process_name = %(program_name)s_%(process_num)02d
            numprocs     = {processes}
            startsecs    = 1
            startretries = 10
            exitcodes    = 2
            stopwaitsecs = 300
            autostart    = true
        EOF;
    }

    private function fileName(string $queue)
    {
        return sprintf('%s/%s.ini', self::SUPERVISOR_PATH, $queue);
    }

}