<?php

declare(strict_types=1);

namespace Application\Command\Edition\Update;

use Colybri\Library\Application\Command\Edition\Update\UpdateEditionCommand;
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

final class UpdateEditionCommandTest extends TestCase
{
    private $editionId;
    private $year;
    private $publisherId;
    private $bookId;
    private $googleId;
    private $isbn;
    private $title;
    private $subtitle;
    private $city;
    private $locale;
    private $image;
    private $pages;
    private $isOnLibrary;
    private $condition;

    private $command;
    private $arguments;

    public function setUp(): void
    {
        $this->editionId = (Uuid::v4())->value();
        $this->year = 2019;
        $this->publisherId = (Uuid::v4())->value();
        $this->bookId = (Uuid::v4())->value();
        $this->googleId = 'UFeGDwAAQBAJ';
        $this->isbn = '9785041534042';
        $this->title = 'Архипелаг ГУЛАГ';
        $this->subtitle = 'Александр Солженицын';
        $this->locale = 'ru';
        $this->image = '';
        $this->city = 'San Petersburgo';
        $this->pages = 1445;
        $this->isOnLibrary = true;
        $this->condition = 'new';

        $this->arguments = [
            UpdateEditionCommand::EDITION_ID_PAYLOAD => $this->editionId,
            UpdateEditionCommand::EDITION_YEAR_PAYLOAD => $this->year,
            UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD => $this->publisherId,
            UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD => $this->bookId,
            UpdateEditionCommand::EDITION_GOOGLE_ID_PAYLOAD => $this->googleId,
            UpdateEditionCommand::EDITION_ISBN_PAYLOAD => $this->isbn,
            UpdateEditionCommand::EDITION_TITLE_PAYLOAD => $this->title,
            UpdateEditionCommand::EDITION_SUBTITLE_PAYLOAD => $this->subtitle,
            UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD => $this->locale,
            UpdateEditionCommand::EDITION_IMAGE_PAYLOAD => $this->image,
            UpdateEditionCommand::EDITION_CITY_PAYLOAD => $this->city,
            UpdateEditionCommand::EDITION_PAGES_PAYLOAD => $this->pages,
            UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD => $this->isOnLibrary,
            UpdateEditionCommand::EDITION_CONDITION_PAYLOAD => $this->condition,
        ];

        $this->command = UpdateEditionCommand::fromPayload(
            Uuid::v4(),
            $this->arguments
        );
    }

    /**
     * @test
     */
    public function given_subject_under_test_when_set_up_then_should_return_same_instance_class(): void
    {
        self::assertInstanceOf(UpdateEditionCommand::class, $this->command);
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
    public function given_edition_members_when_command_getters_are_called_then_return_equals_objects_and_values(): void
    {
        self::assertTrue(Uuid::from($this->bookId)->equalTo($this->command->bookId()));
        self::assertTrue(EditionYear::from($this->year)->equalTo($this->command->year()));
        self::assertTrue(Uuid::from($this->publisherId)->equalTo($this->command->publisherId()));
        self::assertTrue(Uuid::from($this->bookId)->equalTo($this->command->bookId()));
        self::assertTrue(EditionGoogleBooksId::from($this->googleId)->equalTo($this->command->googleBooksId()));
        self::assertTrue(EditionISBN::from($this->isbn)->equalTo($this->command->isbn()));
        self::assertTrue(EditionTitle::from($this->title)->equalTo($this->command->title()));
        self::assertTrue(EditionSubtitle::from($this->subtitle)->equalTo($this->command->subtitle()));
        self::assertTrue(EditionLocale::from($this->locale)->equalTo($this->command->locale()));
        self::assertEquals($this->image, $this->command->image());
        self::assertTrue(EditionCondition::from($this->condition)->equalTo($this->command->condition()));
        self::assertTrue(EditionCity::from($this->city)->equalTo($this->command->city()));
        self::assertTrue(EditionPages::from($this->pages)->equalTo($this->command->pages()));
        self::assertEquals(EditionIsOnLibrary::from($this->isOnLibrary), $this->command->isOnLibrary());

    }

    private function not_nullable_arguments(): array
    {
        return [
            [UpdateEditionCommand::EDITION_ID_PAYLOAD],
            [UpdateEditionCommand::EDITION_YEAR_PAYLOAD],
            [UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD],
            [UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD],
            [UpdateEditionCommand::EDITION_ISBN_PAYLOAD],
            [UpdateEditionCommand::EDITION_TITLE_PAYLOAD],
            [UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD],
            [UpdateEditionCommand::EDITION_CITY_PAYLOAD],
            [UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD],
        ];
    }

    /**
     * @test
     * @dataProvider not_nullable_arguments
     */
    public function given_null_argument_not_nullable_when_command_is_invoke_then_throws_invalid_argument_exception($notNullableArgument): void
    {
        $this->arguments[$notNullableArgument] = null;

        self::expectException(\InvalidArgumentException::class);

        UpdateEditionCommand::fromPayload(Uuid::v4(), $this->arguments);
    }

    private function invalid_arguments(): array
    {
        return [
            [UpdateEditionCommand::EDITION_ID_PAYLOAD, 'no-soy-una-id'],
            [UpdateEditionCommand::EDITION_YEAR_PAYLOAD, false],
            [UpdateEditionCommand::EDITION_BOOK_ID_PAYLOAD, 'no-soy-una-id'],
            [UpdateEditionCommand::EDITION_PUBLISHER_ID_PAYLOAD, 'no-soy-una-id'],
            [UpdateEditionCommand::EDITION_ISBN_PAYLOAD, '9547568'],
            [UpdateEditionCommand::EDITION_TITLE_PAYLOAD, 34.56],
            [UpdateEditionCommand::EDITION_LANGUAGE_PAYLOAD, 'IT'],
            [UpdateEditionCommand::EDITION_CITY_PAYLOAD, 45],
            [UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD, 'no-soy-un-booleano'],
        ];
    }

    /**
     * @test
     * @dataProvider invalid_arguments
     */
    public function given_invalid_argument_when_command_is_invoke_then_throws_invalid_argument_exception($argument, $value): void
    {
        $this->arguments[$argument] = $value;
        self::expectException(\InvalidArgumentException::class);

        UpdateEditionCommand::fromPayload(Uuid::v4(), $this->arguments);
    }

    /**
     * @test
     */
    public function given_edition_in_library_without_condition_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        $this->arguments[UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD] = true;
        $this->arguments[UpdateEditionCommand::EDITION_CONDITION_PAYLOAD] = null;

        self::expectException(\InvalidArgumentException::class);

        UpdateEditionCommand::fromPayload(Uuid::v4(), $this->arguments);
    }

    /**
     * @test
     */
    public function given_edition_not_in_library_with_condition_when_command_is_invoke_then_throws_invalid_argument_exception(): void
    {
        $this->arguments[UpdateEditionCommand::EDITION_IS_ON_LIBRARY_PAYLOAD] = false;
        $this->arguments[UpdateEditionCommand::EDITION_CONDITION_PAYLOAD] = 'used';

        self::expectException(\InvalidArgumentException::class);

        UpdateEditionCommand::fromPayload(Uuid::v4(), $this->arguments);
    }
}