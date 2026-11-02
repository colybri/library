<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Infrastructure\Tracing;

use Colybri\Library\Domain\Messaging\ProcessorRepository;
use Colybri\Library\Infrastructure\Tracing\TracedProcessorRepository;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TracedProcessorRepositoryTest extends TestCase
{
    private const SUBSCRIBER = 'App\\SomeSubscriber';

    private InMemoryTracing $tracing;
    private ProcessorRepository $inner;
    private TracedProcessorRepository $repository;

    protected function setUp(): void
    {
        $this->tracing = new InMemoryTracing();
        $this->inner = new class () implements ProcessorRepository {
            /** @var array<string, true> */
            public array $processed = [];

            public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName): bool
            {
                return !isset($this->processed[$subscriberClassName . $messageId->value()]);
            }

            public function processSubscriber(Uuid $messageId, string $subscriberClassName): void
            {
                $this->processed[$subscriberClassName . $messageId->value()] = true;
            }
        };
        $this->repository = new TracedProcessorRepository($this->inner, $this->tracing->tracer());
    }

    #[Test]
    public function it_delegates_and_traces_each_redis_command(): void
    {
        $messageId = Uuid::v4();

        $consumer = $this->tracing->tracer()->spanBuilder('queue process')->startSpan();
        $scope = $consumer->activate();
        self::assertTrue($this->repository->isNotAlreadyProcessed($messageId, self::SUBSCRIBER));
        $this->repository->processSubscriber($messageId, self::SUBSCRIBER);
        $scope->detach();
        $consumer->end();

        $exists = $this->tracing->span('EXISTS');
        $set = $this->tracing->span('SET');

        self::assertSame(SpanKind::KIND_CLIENT, $exists->getKind());
        self::assertSame('redis', $exists->getAttributes()->get('db.system.name'));
        self::assertSame(self::SUBSCRIBER, $set->getAttributes()->get(TracedProcessorRepository::ATTRIBUTE_SUBSCRIBER));
        self::assertSame($consumer->getContext()->getSpanId(), $exists->getParentSpanId());
        self::assertFalse($this->tracing->span('queue process')->getAttributes()->get(TracedProcessorRepository::ATTRIBUTE_DUPLICATE));
        self::assertFalse($this->repository->isNotAlreadyProcessed($messageId, self::SUBSCRIBER));
    }

    #[Test]
    public function a_duplicate_message_is_marked_on_the_consumer_span(): void
    {
        $messageId = Uuid::v4();
        $this->inner->processSubscriber($messageId, self::SUBSCRIBER);

        $consumer = $this->tracing->tracer()->spanBuilder('queue process')->startSpan();
        $scope = $consumer->activate();
        self::assertFalse($this->repository->isNotAlreadyProcessed($messageId, self::SUBSCRIBER));
        $scope->detach();
        $consumer->end();

        self::assertTrue($this->tracing->span('queue process')->getAttributes()->get(TracedProcessorRepository::ATTRIBUTE_DUPLICATE));
    }

    #[Test]
    public function a_redis_error_is_recorded_and_rethrown(): void
    {
        $failing = new class () implements ProcessorRepository {
            public function isNotAlreadyProcessed(Uuid $messageId, string $subscriberClassName): bool
            {
                throw new \RedisException('Connection refused');
            }

            public function processSubscriber(Uuid $messageId, string $subscriberClassName): void
            {
            }
        };

        $this->expectException(\RedisException::class);

        try {
            (new TracedProcessorRepository($failing, $this->tracing->tracer()))->isNotAlreadyProcessed(Uuid::v4(), self::SUBSCRIBER);
        } finally {
            self::assertSame(StatusCode::STATUS_ERROR, $this->tracing->span('EXISTS')->getStatus()->getCode());
        }
    }
}
