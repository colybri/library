<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Book\Create;

use Colybri\Library\Application\Command\Book\Create\CreateBookCommand;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIds;
use Colybri\Library\Domain\Model\Book\ValueObject\BookAuthorIsPseudo;
use Colybri\Library\Domain\Model\Book\ValueObject\BookIsOnWishList;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYear;
use Colybri\Library\Domain\Model\Book\ValueObject\BookPublishYearIsEstimated;
use Colybri\Library\Domain\Model\Book\ValueObject\BookSubtitle;
use Colybri\Library\Domain\Model\Book\ValueObject\BookTitle;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class CreateBookCommandTest extends TestCase
{
    private $bookId;
    private $title;
    private $subtitle;
    private $authorIds;
    private $publishYear;
    private $publishYearIsEstimated;
    private $isPseudo;
    private $isOnWishList;

    private $command;

    public function setUp(): void
    {
        $this->bookId = (Uuid::v4())->value();
        $this->title = 'The gnostics';
        $this->subtitle = 'Myth, ritual, and diversity in early christianity';
        $this->authorIds = [(Uuid::v4())->value()];
        $this->publishYear = 2010;
        $this->publishYearIsEstimated = false;
        $this->isPseudo = false;
        $this->isOnWishList = true;

        $this->command = CreateBookCommand::fromPayload(
            Uuid::v4(),
            [
                CreateBookCommand::BOOK_ID_PAYLOAD => $this->bookId,
                CreateBookCommand::BOOK_TITLE_PAYLOAD => $this->title,
                CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateBookCommand::BOOK_AUTHORS_PAYLOAD => $this->authorIds,
                CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $this->publishYear,
                CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => $this->publishYearIsEstimated,
                CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => $this->isPseudo,
                CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => $this->isOnWishList,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(CreateBookCommand::class, $this->command);
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_open_async_standard_name(): void
    {
        self::assertMatchesRegularExpression('/^([a-z|_]+)\.([a-z|_]+)\.([0-9]){1,2}\.([a-z|_]+)\.([a-z|_]+)\.([a-z|_]+)$/', $this->command->messageName());
    }

    /**
     * @test
     */
    public function given_book_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->bookId)->equalTo($this->command->bookId()));
        self::assertTrue(BookTitle::from($this->title)->equalTo($this->command->title()));
        self::assertTrue(BookSubtitle::from($this->subtitle)->equalTo($this->command->subtitle()));
        self::assertTrue(BookPublishYear::from($this->publishYear)->equalTo($this->command->publishYear()));
        self::assertTrue(BookAuthorIds::from(array_map(fn($id) => Uuid::from((string)$id), $this->authorIds))->equalTo($this->command->authorIds()));
        self::assertEquals(BookPublishYearIsEstimated::from($this->publishYearIsEstimated), $this->command->publishYearIsEstimated());
        self::assertEquals(BookAuthorIsPseudo::from($this->isPseudo), $this->command->isPseudo());
        self::assertEquals(BookIsOnWishList::from($this->isOnWishList), $this->command->isOnWishList());
    }

    /**
     * @test
     */
    public function given_invalid_author_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateBookCommand::fromPayload(
            Uuid::v4(),
            [
                CreateBookCommand::BOOK_ID_PAYLOAD => 23424324,
                CreateBookCommand::BOOK_TITLE_PAYLOAD => $this->title,
                CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateBookCommand::BOOK_AUTHORS_PAYLOAD => $this->authorIds,
                CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $this->publishYear,
                CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => $this->publishYearIsEstimated,
                CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => $this->isPseudo,
                CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => $this->isOnWishList,
            ]
        );
    }

    /**
     * @test
     */
    public function given_null_book_title_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateBookCommand::fromPayload(
            Uuid::v4(),
            [
                CreateBookCommand::BOOK_ID_PAYLOAD => $this->bookId,
                CreateBookCommand::BOOK_TITLE_PAYLOAD => null,
                CreateBookCommand::BOOK_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateBookCommand::BOOK_AUTHORS_PAYLOAD => $this->authorIds,
                CreateBookCommand::BOOK_PUBLISH_YEAR_PAYLOAD => $this->publishYear,
                CreateBookCommand::BOOK_PUBLISH_YEAR_IS_ESTIMATED_PAYLOAD => $this->publishYearIsEstimated,
                CreateBookCommand::BOOK_AUTHOR_IS_PSEUDO_PAYLOAD => $this->isPseudo,
                CreateBookCommand::BOOK_IS_ON_WISH_LIST_PAYLOAD => $this->isOnWishList,
            ]
        );
    }
}
