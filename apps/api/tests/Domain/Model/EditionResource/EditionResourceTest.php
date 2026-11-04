<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Domain\Model\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Tests\Mock\Domain\Model\EditionResource\EditionResourceObjectMother;
use InvalidArgumentException;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EditionResourceTest extends TestCase
{
    #[Test]
    public function given_a_resource_when_storage_key_is_asked_then_it_groups_files_by_edition(): void
    {
        $id = Uuid::v4();
        $editionId = Uuid::v4();

        $resource = (new EditionResourceObjectMother(id: $id, editionId: $editionId))->build();

        self::assertSame(sprintf('editions/%s/%s', $editionId->value(), $id->value()), $resource->storageKey());
    }

    #[Test]
    public function given_a_resource_when_serialized_then_has_its_metadata(): void
    {
        $json = json_decode((string) json_encode((new EditionResourceObjectMother())->build()), true);

        self::assertSame(['id', 'editionId', 'name', 'fileName', 'mediaType', 'size'], array_keys($json));
    }

    public static function acceptedMediaTypes(): iterable
    {
        foreach (EditionResourceMediaType::ALLOWED as $mediaType) {
            yield $mediaType => [$mediaType];
        }
    }

    #[Test]
    #[DataProvider('acceptedMediaTypes')]
    public function given_an_accepted_media_type_then_it_is_valid(string $mediaType): void
    {
        self::assertSame($mediaType, EditionResourceMediaType::from($mediaType)->value());
    }

    #[Test]
    public function given_an_executable_then_it_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EditionResourceMediaType::from('application/x-executable');
    }

    #[Test]
    public function given_more_than_200_mib_then_the_size_is_rejected(): void
    {
        self::assertSame(EditionResourceSize::MAX_BYTES, EditionResourceSize::from(200 * 1024 * 1024)->value());

        $this->expectException(InvalidArgumentException::class);

        EditionResourceSize::from(EditionResourceSize::MAX_BYTES + 1);
    }

    #[Test]
    public function given_a_file_name_with_a_path_then_it_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EditionResourceFileName::from('../../etc/passwd');
    }
}
