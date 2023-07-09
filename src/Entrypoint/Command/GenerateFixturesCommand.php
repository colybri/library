<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Command;

use Colybri\Library\Infrastructure\Fixtures\FixtureRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateFixturesCommand extends Command
{
    private FixtureRegistry $fixturesRegistry;

    public function __construct(FixtureRegistry $fixturesRegistry)
    {
        $this->fixturesRegistry = $fixturesRegistry;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('generate fixtures')
            ->setName('library:fixtures:generate')
            ->setHelp('This command allows you to generate fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fixturesRegistry->execute();

        return Command::SUCCESS;
    }
}