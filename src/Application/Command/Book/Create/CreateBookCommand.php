<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Command\Book\Create;

use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Book\Book;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Command;
use PcComponentes\TopicGenerator\Topic;

class CreateBookCommand extends Command
{
    protected const NAME = 'create';
    protected const VERSION = '1';


    public const ID_PAYLOAD = 'id';
    public const TITLE_PAYLOAD = 'title';
    public const AUTHORS_PAYLOAD = 'authorsIds';
    public const COUNTRY_ID_PAYLOAD = 'countryId';
    public const PUBLISH_YEAR_PAYLOAD = 'publishYear';
    public const IS_ESTIMATED_PUBLISH = 'estimatedPublishYear';
    public const PUBLISHER_ID_PAYLOAD = 'publisherId';
    public const IS_PSEUDO_PAYLOAD = 'isPseudo';

    public const EDITION_YEAR_PAYLOAD = 'editionYear';
    public const GOOGLE_ID_PAYLOAD = 'googleId';
    public const ISBN_PAYLOAD = 'isbn';
    public const EDITION_TITLE_PAYLOAD = 'editionTitle';
    public const LANGUAGE_PAYLOAD = 'language';
    public const IMAGE_PAYLOAD = 'image';
    public const RESOURCES_PAYLOAD = 'resource';
    public const CONDITION_PAYLOAD = 'condition';
    public const PAGES_PAYLOAD = 'pages';
    public const EDITION_CITY_PAYLOAD = 'city';

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Book::modelName(),
            self::NAME
        );
    }

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    protected function assertPayload(): void
    {
        // TODO: Implement assertPayload() method.
    }
}