<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Mock\Domain\Model\EditionResource;

use Colybri\Library\Domain\Model\EditionResource\EditionResource;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceFileName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceMediaType;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceName;
use Colybri\Library\Domain\Model\EditionResource\ValueObject\EditionResourceSize;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;

class EditionResourceObjectMother
{
    public function __construct(
        private ?Uuid $id = null,
        private ?Uuid $editionId = null,
        private ?EditionResourceName $name = null,
        private ?EditionResourceFileName $fileName = null,
        private ?EditionResourceMediaType $mediaType = null,
        private ?EditionResourceSize $size = null,
    ) {
        $this->id = $id ?? Uuid::v4();
        $this->editionId = $editionId ?? Uuid::v4();
        $this->name = $name ?? EditionResourceName::from('Digital copy');
        $this->fileName = $fileName ?? EditionResourceFileName::from('paideia.pdf');
        $this->mediaType = $mediaType ?? EditionResourceMediaType::from('application/pdf');
        $this->size = $size ?? EditionResourceSize::from(random_int(1, 1000000));
    }

    public function build(): EditionResource
    {
        return EditionResource::hydrate($this->id, $this->editionId, $this->name, $this->fileName, $this->mediaType, $this->size);
    }
}
