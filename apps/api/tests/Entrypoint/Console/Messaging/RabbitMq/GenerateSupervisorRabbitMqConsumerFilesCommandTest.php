<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Entrypoint\Console\Messaging\RabbitMq;

use Colybri\Library\Application\Subscriber\Author\UpdateSnapshotOnAuthorCreatedProcessor;
use Colybri\Library\Application\Subscriber\Author\UpdateSnapshotOnAuthorCreatedSubscriber;
use Colybri\Library\Domain\Messaging\ProcessorRepository;
use Colybri\Library\Entrypoint\Console\Messaging\RabbitMq\GenerateSupervisorRabbitMqConsumerFilesCommand;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class GenerateSupervisorRabbitMqConsumerFilesCommandTest extends KernelTestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir() . '/supervisord-' . bin2hex(random_bytes(4)) . '/conf.d';
    }

    protected function tearDown(): void
    {
        foreach (glob($this->outputDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->outputDir)) {
            rmdir($this->outputDir);
            rmdir(dirname($this->outputDir));
        }

        parent::tearDown();
    }

    #[Test]
    public function it_writes_one_program_per_subscriber_that_runs_the_consume_command(): void
    {
        $subscriber = new UpdateSnapshotOnAuthorCreatedSubscriber(
            new UpdateSnapshotOnAuthorCreatedProcessor(),
            $this->createStub(ProcessorRepository::class)
        );
        $tester = new CommandTester(new GenerateSupervisorRabbitMqConsumerFilesCommand([$subscriber], 'dev'));

        $status = $tester->execute(['command-path' => '/srv/app/', '--output-dir' => $this->outputDir]);

        self::assertSame(Command::SUCCESS, $status);
        $file = $this->outputDir . '/update_snapshot_on_author_created_subscriber.conf';
        self::assertFileExists($file);
        $content = (string) file_get_contents($file);
        self::assertStringContainsString('[program:library_colybri.library.author.update_snapshot_on_author_created_subscriber]', $content);
        self::assertStringContainsString(
            'command      = /srv/app/bin/console library:rabbit:consume --env=dev colybri.library.author.update_snapshot_on_author_created_subscriber',
            $content
        );
    }

    #[Test]
    public function the_generated_command_is_registered_in_the_application(): void
    {
        $application = new Application(self::bootKernel());
        $tester = new CommandTester($application->find(GenerateSupervisorRabbitMqConsumerFilesCommand::NAME));

        $tester->execute(['--output-dir' => $this->outputDir]);

        $files = glob($this->outputDir . '/*.conf') ?: [];
        self::assertNotEmpty($files, 'No subscriber produced a supervisor program');

        foreach ($files as $file) {
            self::assertSame(1, preg_match('#bin/console (\S+) --env=test #', (string) file_get_contents($file), $match));
            self::assertTrue($application->has($match[1]), sprintf('"%s" runs "%s", which is not a console command', basename($file), $match[1]));
        }
    }
}
