<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Messaging\RabbitMq;

use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitConfigurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ConfigureRabbitMqCommand extends Command
{
    private const NAME = 'library:rabbit:configure';

    public function __construct(private RabbitConfigurator $configurer, private string $exchangeName, private \Traversable $subscribers)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('configure RabbitMQ')
            ->setHelp('Configure the RabbitMQ to allow publish & consume domain events');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configurer->configure($this->exchangeName, ...iterator_to_array($this->subscribers));

        return Command::SUCCESS;

    }
}