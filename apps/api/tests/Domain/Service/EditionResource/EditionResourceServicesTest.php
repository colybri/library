<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Service\EditionResource;

use Colybri\Library\Domain\Model\Edition\EditionRepository;
use Colybri\Library\Domain\Model\Edition\Exception\EditionDoesNotExistException;
use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceRepository;
use Colybri\Library\Domain\Model\EditionResource\EditionResourceStorage;
use Colybri\Library\Domain\Model\EditionResource\Exception\EditionResourceAlreadyExistException;
use Colybri\Library\Domain\Model\EditionResource\Exception\EditionResourceDoesNotExistException;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Domain\Service\Edition\EditionFinder;
use Colybri\Library\Domain\Service\EditionResource\EditionResourceCreator;
use Colybri\Library\Domain\Service\EditionResource\EditionResourceDeleter;
use Colybri\Library\Domain\Service\EditionResource\EditionResourceFinder;
use Colybri\Library\Tests\Mock\Domain\Model\Edition\EditionObjectMother;
use Colybri\Library\Tests\Mock\Domain\Model\EditionResource\EditionResourceObjectMother;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class EditionResourceServicesTest extends TestCase
{
    private EditionRepository&MockObject $editions;
    private EditionResourceRepository&MockObject $resources;
    private EditionResourceStorage&MockObject $storage;

    protected function setUp(): void
    {
        $this->editions = $this->createMock(EditionRepository::class);
        $this->resources = $this->createMock(EditionResourceRepository::class);
        $this->storage = $this->createMock(EditionResourceStorage::class);
    }

    #[Test]
    public function given_an_existing_edition_when_a_resource_is_created_then_stores_the_file_and_the_row(): void
    {
        $editionId = Uuid::v4();
        $this->editions->method('find')->willReturn((new EditionObjectMother(id: $editionId))->build());
        $this->resources->method('find')->willReturn(null);

        $this->storage->expects($this->once())->method('save')
            ->with($this->isInstanceOf(EditionResource::class), '/tmp/upload');
        $this->resources->expects($this->once())->method('insert');

        $resource = $this->creator()->execute(Uuid::v4(), $editionId, ...$this->metadata());

        self::assertTrue($editionId->equalTo($resource->editionId()));
    }

    #[Test]
    public function given_a_missing_edition_when_a_resource_is_created_then_nothing_is_stored(): void
    {
        $this->expectException(EditionDoesNotExistException::class);

        $this->editions->method('find')->willReturn(null);
        $this->storage->expects($this->never())->method('save');
        $this->resources->expects($this->never())->method('insert');

        $this->creator()->execute(Uuid::v4(), Uuid::v4(), ...$this->metadata());
    }

    #[Test]
    public function given_an_existing_resource_id_when_a_resource_is_created_then_throws(): void
    {
        $this->expectException(EditionResourceAlreadyExistException::class);

        $this->editions->method('find')->willReturn((new EditionObjectMother())->build());
        $this->resources->method('find')->willReturn((new EditionResourceObjectMother())->build());
        $this->storage->expects($this->never())->method('save');

        $this->creator()->execute(Uuid::v4(), Uuid::v4(), ...$this->metadata());
    }

    #[Test]
    public function given_a_resource_of_another_edition_when_it_is_found_then_it_does_not_exist(): void
    {
        $this->expectException(EditionResourceDoesNotExistException::class);

        $resource = (new EditionResourceObjectMother())->build();
        $this->resources->method('find')->willReturn($resource);

        (new EditionResourceFinder($this->resources))->execute($resource->aggregateId(), Uuid::v4());
    }

    #[Test]
    public function given_a_resource_when_it_is_deleted_then_its_row_and_file_go(): void
    {
        $resource = (new EditionResourceObjectMother())->build();
        $this->resources->method('find')->willReturn($resource);

        $this->resources->expects($this->once())->method('delete')->with($resource->aggregateId());
        $this->storage->expects($this->once())->method('delete')->with($resource);

        (new EditionResourceDeleter($this->resources, $this->storage, new EditionResourceFinder($this->resources)))
            ->execute($resource->aggregateId(), $resource->editionId());
    }

    private function creator(): EditionResourceCreator
    {
        return new EditionResourceCreator($this->resources, $this->storage, new EditionFinder($this->editions));
    }

    private function metadata(): array
    {
        return [
            EditionResourceName::from('Digital copy'),
            EditionResourceFileName::from('paideia.pdf'),
            EditionResourceMediaType::from('application/pdf'),
            EditionResourceSize::from(1024),
            '/tmp/upload',
        ];
    }
}
