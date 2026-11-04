<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\EditionResource\Create;

use Colybri\Library\Application\Command\EditionResource\Create\CreateEditionResourceCommand;
use InvalidArgumentException;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateEditionResourceCommandTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'resource');
        file_put_contents($this->path, '%PDF-1.4');
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
    }

    #[Test]
    public function given_a_valid_upload_then_the_command_carries_it(): void
    {
        $command = CreateEditionResourceCommand::fromPayload(Uuid::v4(), $this->payload());

        self::assertMatchesRegularExpression('/^([a-z|_]+)\.([a-z|_]+)\.([0-9]){1,2}\.([a-z|_]+)\.([a-z|_]+)\.([a-z|_]+)$/', $command::messageName());
        self::assertSame('Digital copy', $command->name()->value());
        self::assertSame('application/pdf', $command->mediaType()->value());
        self::assertSame($this->path, $command->path());
    }

    #[Test]
    public function given_a_media_type_out_of_the_list_then_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CreateEditionResourceCommand::fromPayload(Uuid::v4(), [
            CreateEditionResourceCommand::MEDIA_TYPE_PAYLOAD => 'application/zip',
        ] + $this->payload());
    }

    #[Test]
    public function given_a_missing_file_then_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        CreateEditionResourceCommand::fromPayload(Uuid::v4(), [
            CreateEditionResourceCommand::PATH_PAYLOAD => '/no/such/file',
        ] + $this->payload());
    }

    private function payload(): array
    {
        return [
            CreateEditionResourceCommand::RESOURCE_ID_PAYLOAD => Uuid::v4()->value(),
            CreateEditionResourceCommand::EDITION_ID_PAYLOAD => Uuid::v4()->value(),
            CreateEditionResourceCommand::NAME_PAYLOAD => 'Digital copy',
            CreateEditionResourceCommand::FILE_NAME_PAYLOAD => 'paideia.pdf',
            CreateEditionResourceCommand::MEDIA_TYPE_PAYLOAD => 'application/pdf',
            CreateEditionResourceCommand::SIZE_PAYLOAD => 8,
            CreateEditionResourceCommand::PATH_PAYLOAD => $this->path,
        ];
    }
}
