<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Integration\Messaging;

use Colybri\Library\Application\Subscriber\Author\UpdateSnapshotOnAuthorCreatedSubscriber;
use Colybri\Library\Domain\Messaging\ProcessorRepository;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs against a real Redis. In CI: the "Message bus integration" job.
 * Locally: make test-integration.
 */
#[Group('integration')]
final class RedisProcessorRepositoryTest extends KernelTestCase
{
    private ProcessorRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(ProcessorRepository::class);
    }

    #[Test]
    public function a_message_counts_as_processed_only_after_its_subscriber_marks_it(): void
    {
        $messageId = Uuid::v4();

        self::assertTrue($this->repository->isNotAlreadyProcessed($messageId, UpdateSnapshotOnAuthorCreatedSubscriber::class));

        $this->repository->processSubscriber($messageId, UpdateSnapshotOnAuthorCreatedSubscriber::class);

        self::assertFalse($this->repository->isNotAlreadyProcessed($messageId, UpdateSnapshotOnAuthorCreatedSubscriber::class));
    }

    #[Test]
    public function each_subscriber_tracks_the_same_message_on_its_own(): void
    {
        $messageId = Uuid::v4();

        $this->repository->processSubscriber($messageId, UpdateSnapshotOnAuthorCreatedSubscriber::class);

        self::assertTrue($this->repository->isNotAlreadyProcessed($messageId, 'Another\\Subscriber'));
    }

    #[Test]
    public function the_processed_mark_expires(): void
    {
        $messageId = Uuid::v4();
        $this->repository->processSubscriber($messageId, UpdateSnapshotOnAuthorCreatedSubscriber::class);

        $redis = self::getContainer()->get('library.redis');
        $ttl = $redis->ttl(sprintf('library:processed:%s:%s', UpdateSnapshotOnAuthorCreatedSubscriber::class, $messageId->value()));

        self::assertGreaterThan(0, $ttl);
        self::assertLessThanOrEqual(604800, $ttl);
    }
}
