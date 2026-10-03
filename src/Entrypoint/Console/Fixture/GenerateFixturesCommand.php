<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Console\Fixture;

use Colybri\Library\Infrastructure\Fixtures\FixtureRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFixturesCommand extends Command
{
    private const NAME = 'library:fixtures:generate';
    public function __construct(private FixtureRegistry $fixturesRegistry)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('generate fixtures')
            ->setName(self::NAME)
            ->setHelp('This command allows you to generate fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixturesRegistry->execute();

        return Command::SUCCESS;
    }
}
