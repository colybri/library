<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Model\Paper;

use Colybri\Library\Domain\Model\Paper\ValueObject\PaperEndPage;
use Colybri\Library\Domain\Model\Paper\ValueObject\PaperInitialPage;
use Colybri\Library\Domain\Model\Paper\ValueObject\PaperPath;
use Colybri\Library\Domain\Model\Paper\ValueObject\PaperPublishYear;
use Colybri\Library\Domain\Model\Paper\ValueObject\PaperTitle;
use Colybri\Library\Domain\Model\Paper\ValueObject\PaperJournalVolume;
use Forkrefactor\Ddd\Domain\Model\AggregateRoot;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;

class Paper extends AggregateRoot
{
    private const NAME = 'paper';
    private Uuid $aggregateId;
    private PaperTitle $title;
    private ?PaperInitialPage $initialPage;
    private ?PaperEndPage $endPage;
    private Uuid $journalId;
    private ?PaperJournalVolume $volume;
    private ?PaperPublishYear $publishYear;
    private ?PaperPath $paperPath;

    public static function create(
        Uuid $id,
        PaperTitle $title,
        Uuid $journalId,
        ?PaperJournalVolume $volume,
        ?PaperInitialPage $initialPage,
        ?PaperEndPage $endPage,
        ?PaperPublishYear $publishYear,
        ?PaperPath $paperPath
    ) {
        $self = new self($id);
        $self->aggregateId = $id;
        $self->title = $title;
        $self->journalId = $journalId;
        $self->volume = $volume;
        $self->initialPage = $initialPage;
        $self->endPage = $endPage;
        $self->publishYear = $publishYear;
        $self->paperPath = $paperPath;

        return $self;
    }

    public static function modelName(): string
    {
        return self::NAME;
    }

    public function id(): Uuid
    {
        return $this->aggregateId;
    }

    public function title(): PaperTitle
    {
        return $this->title;
    }

    public function journalId(): Uuid
    {
        return $this->journalId;
    }

    public function journalVolume(): ?PaperJournalVolume
    {
        return $this->volume;
    }

    public function initialPage(): ?PaperInitialPage
    {
        return $this->initialPage;
    }

    public function endPage(): ?PaperEndPage
    {
        return $this->endPage;
    }

    public function publishYear(): ?PaperPublishYear
    {
        return $this->publishYear;
    }

    public function paperPath(): ?PaperPath
    {
        return $this->paperPath;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id(),
            'title' => $this->title(),
            'initialPage' => $this->initialPage(),
            'endPage' => $this->endPage(),
            'journalId' => $this->journalId(),
            'volume' => $this->journalVolume(),
            'publishYear' => $this->publishYear(),
            'path' => $this->paperPath(),
        ];
    }
}
