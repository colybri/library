<?php

declare(strict_types=1);

namespace Colybri\Library\Application\Query\Country\Match;

use Assert\Assert;
use Colybri\Library\Application\Query\Query;
use Colybri\Library\Domain\Messaging\Message\ValueObject\MessageName;
use Colybri\Library\Domain\VendorName;
use Colybri\Library\Domain\ServiceName;
use Colybri\Library\Domain\Model\Country\Country;

final class MatchCountryQuery extends Query
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
        return MessageName::generate(
            VendorName::instance(),
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
            ->keyExists(MatchCountryQuery::KEYWORDS_PAYLOAD)
            ->keyExists(MatchCountryQuery::OFFSET_PAYLOAD)
            ->keyExists(MatchCountryQuery::LIMIT_PAYLOAD)
            ->verifyNow();

        Assert::lazy()
            ->that($payload[MatchCountryQuery::OFFSET_PAYLOAD])->integerish()->min(0)
            ->that($payload[MatchCountryQuery::LIMIT_PAYLOAD])->integerish()->min(1)->max(100)
            ->that($payload[MatchCountryQuery::KEYWORDS_PAYLOAD])->notEmpty()->string()
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
