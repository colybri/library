<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Subscriber\Author;

use Colybri\Library\Domain\Messaging\DomainEvent;
use Colybri\Library\Domain\Messaging\DomainEventSubscriber;
use Colybri\Library\Domain\Messaging\ProcessorRepository;
use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;

final class UpdateSnapshotOnAuthorCreatedSubscriber implements DomainEventSubscriber
{
    public function __construct(private UpdateSnapshotOnAuthorCreatedProcessor $processor, private readonly ProcessorRepository $processedSubscribers)
    {
    }

    public static function subscribedTo(): array
    {
        return [AuthorCreated::class];
    }

    public function __invoke(DomainEvent $event): void
    {
        if ($this->processedSubscribers->isNotAlreadyProcessed($event->messageId(), get_class())) {

            $this->processor->execute();


            $this->processedSubscribers->processSubscriber($event->messageId(), get_class());
        }
    }
}