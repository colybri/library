<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Integration\Messaging;

use Colybri\Library\Application\Command\Author\Create\CreateAuthorCommand;
use Colybri\Library\Application\Command\Author\Delete\DeleteAuthorCommand;
use Colybri\Library\Application\Command\Book\Create\CreateBookCommand;
use Colybri\Library\Application\Command\Book\Delete\DeleteBookCommand;
use Colybri\Library\Application\Subscriber\Author\UpdateSnapshotOnAuthorCreatedSubscriber;
use Colybri\Library\Domain\Model\Author\Event\AuthorCreated;
use Colybri\Library\Domain\Model\Author\Event\AuthorDeleted;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorBornAt;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Book\Event\BookCreated;
use Colybri\Library\Domain\Model\Book\Event\BookDeleted;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres\PostgresDomainEventBus;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\Postgres\PostgresDomainEventsConsumer;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\Queue\RabbitMqQueueNameFormatter;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitConfigurator;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitMqConnection;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitMqDomainEventBus;
use Colybri\Library\Infrastructure\Messaging\Bus\Event\RabbitMq\RabbitMqDomainEventsConsumer;
use Doctrine\DBAL\Connection;
use PcComponentes\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Runs against a real Postgres (schema from migrations/library.sql) and a
 * real RabbitMQ. In CI: the "Message bus integration" job. Locally:
 * make test-integration.
 */
#[Group('integration')]
final class DomainEventBusTest extends KernelTestCase
{
    private const SPAIN_COUNTRY_ID = '34d41de4-d197-8f63-5014-7c5e5e8f71e3';
    private const SPY_QUEUE = 'library.tests.spy';

    private Connection $db;
    private RabbitMqConnection $rabbit;
    private string $exchange;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->db = self::getContainer()->get('connection_write.dbal');
        $this->rabbit = self::getContainer()->get(RabbitMqConnection::class);
        $this->exchange = (string) $_ENV['RABBITMQ_EXCHANGE'];

        foreach (['books_authors', 'editions', 'books', 'authors', 'events_pending_queue', 'events_failed_queue'] as $table) {
            $this->db->executeStatement('DELETE FROM ' . $table);
        }

        self::getContainer()->get(RabbitConfigurator::class)->configure($this->exchange, $this->subscriber());

        $spy = $this->rabbit->queue(self::SPY_QUEUE);
        $spy->setFlags(AMQP_AUTODELETE);
        $spy->declareQueue();
        $spy->bind($this->exchange, '#');
        $spy->purge();
        foreach ([RabbitMqQueueNameFormatter::format($this->subscriber()), RabbitMqQueueNameFormatter::formatDeadLetter($this->subscriber())] as $queue) {
            $this->rabbit->queue($queue)->purge();
        }
    }

    #[Test]
    public function creating_an_author_stores_it_and_publishes_author_created(): void
    {
        $authorId = $this->createAuthor();

        self::assertSame(1, (int) $this->db->fetchOne('SELECT count(*) FROM authors WHERE id = ?', [$authorId]));
        $message = $this->nextPublishedMessage();
        self::assertSame(AuthorCreated::messageName(), $message['data']['type']);
        self::assertSame($authorId, $message['data']['attributes']['aggregate_id']);
    }

    #[Test]
    public function deleting_an_author_removes_it_and_publishes_author_deleted(): void
    {
        $authorId = $this->createAuthor();
        $this->nextPublishedMessage();

        $this->dispatch(DeleteAuthorCommand::fromPayload(Uuid::v4(), [DeleteAuthorCommand::AUTHOR_ID_PAYLOAD => $authorId]));

        self::assertSame(0, (int) $this->db->fetchOne('SELECT count(*) FROM authors WHERE id = ?', [$authorId]));
        $message = $this->nextPublishedMessage();
        self::assertSame(AuthorDeleted::messageName(), $message['data']['type']);
        self::assertSame($authorId, $message['data']['attributes']['aggregate_id']);
    }

    #[Test]
    public function creating_and_deleting_a_book_publishes_book_created_and_book_deleted(): void
    {
        $authorId = $this->createAuthor();
        $this->nextPublishedMessage();
        $bookId = Uuid::v4()->value();

        $this->dispatch(CreateBookCommand::fromPayload(Uuid::v4(), [
            CreateBookCommand::BOOK_ID_PAYLOAD => $bookId,
            CreateBookCommand::BOOK_TITLE_PAYLOAD => 'La Regenta',
            CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => null,
            CreateBookCommand::BOOK_AUTHORS_PAYLOAD => [$authorId],
            CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => 1884,
            CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => false,
            CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => false,
            CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => false,
        ]));

        self::assertSame(1, (int) $this->db->fetchOne('SELECT count(*) FROM books WHERE id = ?', [$bookId]));
        $message = $this->nextPublishedMessage();
        self::assertSame(BookCreated::messageName(), $message['data']['type']);
        self::assertSame($bookId, $message['data']['attributes']['aggregate_id']);

        $this->dispatch(DeleteBookCommand::fromPayload(Uuid::v4(), [DeleteBookCommand::BOOK_ID_PAYLOAD => $bookId]));

        self::assertSame(0, (int) $this->db->fetchOne('SELECT count(*) FROM books WHERE id = ?', [$bookId]));
        $message = $this->nextPublishedMessage();
        self::assertSame(BookDeleted::messageName(), $message['data']['type']);
        self::assertSame($bookId, $message['data']['attributes']['aggregate_id']);
    }

    #[Test]
    public function the_rabbitmq_consumer_hands_author_created_to_its_subscriber(): void
    {
        $this->createAuthor();
        $subscriberQueue = RabbitMqQueueNameFormatter::format($this->subscriber());

        self::getContainer()->get(RabbitMqDomainEventsConsumer::class)->consume($subscriberQueue);

        self::assertNotInstanceOf(\AMQPEnvelope::class, $this->rabbit->queue($subscriberQueue)->get(), 'The event was not acknowledged');
        self::assertNotInstanceOf(
            \AMQPEnvelope::class,
            $this->rabbit->queue(RabbitMqQueueNameFormatter::formatDeadLetter($this->subscriber()))->get(),
            'The event ended in the dead letter queue'
        );
    }

    #[Test]
    #[RunInSeparateProcess]
    public function when_rabbitmq_is_down_events_go_to_postgres_and_are_consumed_from_there(): void
    {
        $unreachable = new RabbitMqConnection([
            'host' => '127.0.0.1',
            'port' => 1,
            'login' => 'guest',
            'password' => 'guest',
            'connect_timeout' => 1,
        ]);
        $bus = new RabbitMqDomainEventBus(
            $this->exchange,
            $unreachable,
            self::getContainer()->get(PostgresDomainEventBus::class)
        );
        $event = AuthorCreated::from(
            Uuid::v4(),
            AuthorFirstName::from('Emilia'),
            null,
            Uuid::from(self::SPAIN_COUNTRY_ID),
            null,
            AuthorBornAt::from(1851),
            null
        );

        $bus->dispatch($event);

        self::assertSame(
            AuthorCreated::messageName(),
            $this->db->fetchOne('SELECT name FROM events_pending_queue WHERE id = ?', [$event->messageId()->value()])
        );

        self::getContainer()->get(PostgresDomainEventsConsumer::class)->consume(10);

        self::assertSame(0, (int) $this->db->fetchOne('SELECT count(*) FROM events_pending_queue'));
        self::assertSame(0, (int) $this->db->fetchOne('SELECT count(*) FROM events_failed_queue'));
    }

    private function createAuthor(): string
    {
        $authorId = Uuid::v4()->value();

        $this->dispatch(CreateAuthorCommand::fromPayload(Uuid::v4(), [
            CreateAuthorCommand::AUTHOR_ID_PAYLOAD => $authorId,
            CreateAuthorCommand::AUTHOR_FIRST_NAME_PAYLOAD => 'Leopoldo',
            CreateAuthorCommand::AUTHOR_LAST_NAME_PAYLOAD => 'Alas',
            CreateAuthorCommand::AUTHOR_COUNTRY_ID_PAYLOAD => self::SPAIN_COUNTRY_ID,
            CreateAuthorCommand::AUTHOR_IS_PSEUDONYM_OF_PAYLOAD => null,
            CreateAuthorCommand::AUTHOR_BORN_YEAR_PAYLOAD => 1852,
            CreateAuthorCommand::AUTHOR_DEATH_YEAR_PAYLOAD => 1901,
        ]));

        return $authorId;
    }

    private function dispatch(object $command): void
    {
        /** @var MessageBusInterface $bus */
        $bus = self::getContainer()->get('command.bus');
        $bus->dispatch($command);
    }

    private function nextPublishedMessage(): array
    {
        $spy = $this->rabbit->queue(self::SPY_QUEUE);
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $envelope = $spy->get(AMQP_AUTOACK);
            if ($envelope instanceof \AMQPEnvelope) {
                return \json_decode($envelope->getBody(), true, 512, JSON_THROW_ON_ERROR);
            }
            \usleep(100_000);
        }

        self::fail('No event was published to RabbitMQ');
    }

    private function subscriber(): UpdateSnapshotOnAuthorCreatedSubscriber
    {
        return self::getContainer()->get(UpdateSnapshotOnAuthorCreatedSubscriber::class);
    }
}
