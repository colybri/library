<?php

declare(strict_types=1);

namespace Colybri\Library\Entrypoint\Command;

use Colybri\Library\Infrastructure\Fixtures\FixtureReference;
use Colybri\Library\Infrastructure\Fixtures\FixtureRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanFixturesCommand extends Command
{
    private FixtureRegistry $fixturesRegistry;

    public function __construct(FixtureRegistry $fixturesRegistry)
    {
        $this->fixturesRegistry = $fixturesRegistry;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('clean fixtures')
            ->setName('library:fixtures:clean')
            ->setHelp('This command allows you to clean fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var FixtureReference $fixtureRegistry */
        foreach ($this->fixturesRegistry->registry() as $fixtureRegistry) {
            $fixtureRegistry->clean();
        }
        return Command::SUCCESS;
    }
}