<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\EditionResource\Create;

use Assert\Assert;
use Colybri\Library\Application\Command\Command;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\VendorName;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

final class CreateEditionResourceCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';

    public const RESOURCE_ID_PAYLOAD = 'id';
    public const EDITION_ID_PAYLOAD = 'editionId';
    public const NAME_PAYLOAD = 'name';
    public const FILE_NAME_PAYLOAD = 'fileName';
    public const MEDIA_TYPE_PAYLOAD = 'mediaType';
    public const SIZE_PAYLOAD = 'size';
    /** Local path of the uploaded file (the PHP temporary file) */
    public const PATH_PAYLOAD = 'path';

    private Uuid $resourceId;
    private Uuid $editionId;
    private EditionResourceName $name;
    private EditionResourceFileName $fileName;
    private EditionResourceMediaType $mediaType;
    private EditionResourceSize $size;
    private string $path;

    public static function messageName(): string
    {
        return MessageName::generate(
            VendorName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            EditionResource::modelName(),
            self::NAME
        );
    }

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        Assert::lazy()
            ->that($payload, 'payload')->isArray()
            ->keyExists(self::RESOURCE_ID_PAYLOAD)
            ->keyExists(self::EDITION_ID_PAYLOAD)
            ->keyExists(self::NAME_PAYLOAD)
            ->keyExists(self::FILE_NAME_PAYLOAD)
            ->keyExists(self::MEDIA_TYPE_PAYLOAD)
            ->keyExists(self::SIZE_PAYLOAD)
            ->keyExists(self::PATH_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[self::RESOURCE_ID_PAYLOAD], self::RESOURCE_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::EDITION_ID_PAYLOAD], self::EDITION_ID_PAYLOAD)->notEmpty()->uuid()
            ->that($payload[self::NAME_PAYLOAD], self::NAME_PAYLOAD)->notBlank()->string()->maxLength(255)
            ->that($payload[self::FILE_NAME_PAYLOAD], self::FILE_NAME_PAYLOAD)->notBlank()->string()->maxLength(255)
            ->that($payload[self::MEDIA_TYPE_PAYLOAD], self::MEDIA_TYPE_PAYLOAD)->string()->inArray(EditionResourceMediaType::ALLOWED)
            ->that($payload[self::SIZE_PAYLOAD], self::SIZE_PAYLOAD)->integer()->between(1, EditionResourceSize::MAX_BYTES)
            ->that($payload[self::PATH_PAYLOAD], self::PATH_PAYLOAD)->string()->file()
            ->verifyNow();

        $this->resourceId = Uuid::from((string) $payload[self::RESOURCE_ID_PAYLOAD]);
        $this->editionId = Uuid::from((string) $payload[self::EDITION_ID_PAYLOAD]);
        $this->name = EditionResourceName::from((string) $payload[self::NAME_PAYLOAD]);
        $this->fileName = EditionResourceFileName::from((string) $payload[self::FILE_NAME_PAYLOAD]);
        $this->mediaType = EditionResourceMediaType::from((string) $payload[self::MEDIA_TYPE_PAYLOAD]);
        $this->size = EditionResourceSize::from((int) $payload[self::SIZE_PAYLOAD]);
        $this->path = (string) $payload[self::PATH_PAYLOAD];
    }

    public function resourceId(): Uuid
    {
        return $this->resourceId;
    }

    public function editionId(): Uuid
    {
        return $this->editionId;
    }

    public function name(): EditionResourceName
    {
        return $this->name;
    }

    public function fileName(): EditionResourceFileName
    {
        return $this->fileName;
    }

    public function mediaType(): EditionResourceMediaType
    {
        return $this->mediaType;
    }

    public function size(): EditionResourceSize
    {
        return $this->size;
    }

    public function path(): string
    {
        return $this->path;
    }
}
