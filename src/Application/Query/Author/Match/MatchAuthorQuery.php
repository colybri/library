<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Author\Match;

use Assert\Assert;
use Colybri\Library\Domain\CompanyName;
use Colybri\Library\Domain\Model\Country\Country;
use Colybri\Library\Domain\ServiceName;
use Forkrefactor\Ddd\Application\Query;
use PcComponentes\TopicGenerator\Topic;

final class MatchAuthorQuery extends Query
{
    private const VERSION = '1';
    private const NAME = 'match';

    public const KEYWORDS_PAYLOAD = 'keywords';
    public const OFFSET_PAYLOAD = 'offset';
    public const LIMIT_PAYLOAD = 'limit';

    private string $match;
    private int $offset;
    private int $limit;

    public static function messageName(): string
    {
        return Topic::generate(
            CompanyName::instance(),
            ServiceName::instance(),
            self::messageVersion(),
            self::messageType(),
            Country::modelName(),
            self::NAME
        );
    }

    public static function messageVersion(): string
    {
        return self::VERSION;
    }

    protected function assertPayload(): void
    {
        $payload = $this->messagePayload();

        Assert::lazy()
            ->that($payload, 'payload')
            ->keyExists(MatchAuthorQuery::OFFSET_PAYLOAD)
            ->keyExists(MatchAuthorQuery::LIMIT_PAYLOAD)
            ->keyExists(MatchAuthorQuery::KEYWORDS_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[MatchAuthorQuery::OFFSET_PAYLOAD])->integerish()->min(0)
            ->that($payload[MatchAuthorQuery::LIMIT_PAYLOAD])->integerish()->min(1)->max(100)
            ->that($payload[MatchAuthorQuery::KEYWORDS_PAYLOAD])->string()->notEmpty()
            ->verifyNow();

        $this->offset = (int)$payload[self::OFFSET_PAYLOAD];
        $this->limit = (int)$payload[self::LIMIT_PAYLOAD];
        $this->match = (string)$payload[self::KEYWORDS_PAYLOAD];
    }

    public function match(): string
    {
        return $this->match;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
