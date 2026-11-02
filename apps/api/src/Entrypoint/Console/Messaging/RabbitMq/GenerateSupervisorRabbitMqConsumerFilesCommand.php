<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Messaging\RabbitMq;

use Colybri\Library\Domain\Messaging\DomainEventSubscriber;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Queue\RabbitMqQueueNameFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateSupervisorRabbitMqConsumerFilesCommand extends Command
{
    public const NAME = 'library:rabbit:generate-supervisor';

    private const NUMBERS_OF_PROCESSES_PER_SUBSCRIBER = 1;
    private const DEFAULT_OUTPUT_DIR = '/srv/app/build/supervisord';

    /**
     * @param iterable<DomainEventSubscriber> $subscribers
     */
    public function __construct(private readonly iterable $subscribers, private readonly string $environment)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Generate the supervisor configuration for every RabbitMQ subscriber')
            ->addArgument(
                'command-path',
                InputArgument::OPTIONAL,
                'Path where the application lives (bin/console is run from there)',
                '/srv/app'
            )
            ->addOption(
                'output-dir',
                'o',
                InputOption::VALUE_REQUIRED,
                'Folder where the .conf files are written',
                self::DEFAULT_OUTPUT_DIR
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = rtrim((string) $input->getArgument('command-path'), '/');
        $outputDir = rtrim((string) $input->getOption('output-dir'), '/');

        if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
            $output->writeln(sprintf('<error>Unable to create the folder "%s"</error>', $outputDir));

            return Command::FAILURE;
        }

        foreach ($this->subscribers as $subscriber) {
            $file = $this->configCreator($path, $outputDir, $subscriber);
            $output->writeln(sprintf('Generated %s', $file), OutputInterface::VERBOSITY_VERBOSE);
        }

        return Command::SUCCESS;
    }

    private function configCreator(string $path, string $outputDir, DomainEventSubscriber $subscriber): string
    {
        $queueName = RabbitMqQueueNameFormatter::format($subscriber);
        $subscriberName = RabbitMqQueueNameFormatter::shortFormat($subscriber);

        $fileContent = str_replace(
            [
                '{queue_name}',
                '{path}',
                '{consume_command}',
                '{environment}',
                '{processes}',
            ],
            [
                $queueName,
                $path,
                ConsumeRabbitMqDomainEventsCommand::NAME,
                $this->environment,
                (string) self::NUMBERS_OF_PROCESSES_PER_SUBSCRIBER,
            ],
            $this->template()
        );

        $file = sprintf('%s/%s.conf', $outputDir, $subscriberName);
        file_put_contents($file, $fileContent);

        return $file;
    }

    private function template(): string
    {
        return <<<EOF
            [program:library_{queue_name}]
            command      = {path}/bin/console {consume_command} --env={environment} {queue_name}
            process_name = %(program_name)s_%(process_num)02d
            numprocs     = {processes}
            startsecs    = 1
            startretries = 10
            exitcodes    = 2
            stopwaitsecs = 300
            autostart    = true

            EOF;
    }
}
