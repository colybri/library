<?php

declare(strict_types=1);

namespace Colybri\Library\Tests\Application\Command\Edition\Create;

use Colybri\Library\Application\Command\Edition\Create\CreateEditionCommand;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCity;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionCondition;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionGoogleBooksId;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionISBN;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionIsOnLibrary;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionLocale;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionPages;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionSubtitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionTitle;
use Colybri\Library\Domain\Model\Edition\ValueObject\EditionYear;
use Forkrefactor\Ddd\Domain\Model\ValueObject\Uuid;
use PHPUnit\Framework\TestCase;

final class CreateEditionCommandTest extends TestCase
{
    private $editionId;
    private $year;
    private $publiserId;
    private $bookId;
    private $googleId;
    private $isbn;
    private $title;
    private $subtitle;
    private $locale;
    private $image;
    private $resource;
    private $condition;
    private $city;
    private $pages;
    private $isOnLibrary;

    private $command;

    public function setUp(): void
    {
        $this->editionId = (Uuid::v4())->value();
        $this->year = 2021;
        $this->publiserId = (Uuid::v4())->value();
        $this->bookId = (Uuid::v4())->value();
        $this->googleId = 'fedFEAAAQBAJ';
        $this->isbn = '978-607-16-2271-6';
        $this->title = 'La rama dorada';
        $this->subtitle = 'Magia y religión';
        $this->locale = 'es';
        $this->image = null;
        $this->resource = null;
        $this->condition = 'new';
        $this->city = 'México D.F.';
        $this->pages = 690;
        $this->isOnLibrary = true;

        $this->command = CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->editionId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => $this->isOnLibrary,
            ]
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(CreateEditionCommand::class, $this->command);
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
        self::assertTrue(EditionYear::from($this->year)->equalTo($this->command->year()));
        self::assertTrue(Uuid::from($this->publiserId)->equalTo($this->command->publisherId()));
        self::assertTrue(Uuid::from($this->bookId)->equalTo($this->command->bookId()));
        self::assertTrue(EditionGoogleBooksId::from($this->googleId)->equalTo($this->command->googleBooksId()));
        self::assertTrue(EditionISBN::from($this->isbn)->equalTo($this->command->isbn()));
        self::assertTrue(EditionTitle::from($this->title)->equalTo($this->command->title()));
        self::assertTrue(EditionSubtitle::from($this->subtitle)->equalTo($this->command->subtitle()));
        self::assertTrue(EditionLocale::from($this->locale)->equalTo($this->command->locale()));
        self::assertEquals($this->image, $this->command->image());
        self::assertEquals($this->resource, $this->command->resource());
        self::assertTrue(EditionCondition::from($this->condition)->equalTo($this->command->condition()));
        self::assertTrue(EditionCity::from($this->city)->equalTo($this->command->city()));
        self::assertTrue(EditionPages::from($this->pages)->equalTo($this->command->pages()));
        self::assertEquals(EditionIsOnLibrary::from($this->isOnLibrary), $this->command->isOnLibrary());
    }

    /**
     * @test
     */
    public function given_invalid_edition_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => 'false-id',
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => $this->isOnLibrary,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_publisher_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->editionId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => '42342342sfdsf',
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => $this->isOnLibrary,
            ]
        );
    }

    /**
     * @test
     */
    public function given_invalid_book_id_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->editionId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => '2342342sdf234',
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => $this->isOnLibrary,
            ]
        );
    }
    /**
     * @test
     */
    public function given_edition_in_library_without_condition_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => null,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => true,
            ]
        );
    }

    /**
     * @test
     */
    public function given_edition_not_in_library_with_condition_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => 'used',
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => false,
            ]
        );
    }

    /**
     * @test
     */
    public function given_edition_with_invalid_isbn_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        self::expectException(\InvalidArgumentException::class);

        CreateEditionCommand::fromPayload(
            Uuid::v4(),
            [
                CreateEditionCommand::EDITION_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
                CreateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publiserId,
                CreateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
                CreateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
                CreateEditionCommand::EDITION_ISBN_PAYLOAD => '401234567890',
                CreateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
                CreateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
                CreateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
                CreateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
                CreateEditionCommand::EDITION_RESOURCES_PAYLOAD => $this->resource,
                CreateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
                CreateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
                CreateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
                CreateEditionCommand::EDITION_IS_ON_LIBRARY => $this->isOnLibrary,
            ]
        );
    }
}
