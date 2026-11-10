<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorFirstName;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorLastName;
use Colybri\Library\Domain\Service\Shared\SimilarityAligner;

final class AuthorMatcher
{
    /** One query per word: a phrase of thousands of words would be thousands of queries */
    public const MAX_KEYWORDS = 5;
    /** Candidates per word before ranking: no word loads the whole table */
    public const MAX_MATCHES_PER_KEYWORD = 200;

    public function __construct(private AuthorRepository $authorRepository, private SimilarityAligner $aligner)
    {
    }

    public function execute($phrase)
    {
        // Without empty words ("a  b" would match every author) and repetitions
        $keywords = array_slice(
            array_values(array_unique(array_filter(
                preg_split('/\s+/u', trim((string) $phrase)) ?: [],
                static fn (string $keyword): bool => '' !== $keyword
            ))),
            0,
            self::MAX_KEYWORDS
        );

        $authors = [];
        foreach ($keywords as $keyword) {
            $match = $this->authorRepository->match($this->getCriteria($keyword));

            /**
             * @var Author $author
             */
            foreach ($match as $author) {
                $authors[$author->aggregateId()->value()] = $author;
            }
        }

        $authors = array_values($authors);

        return $this->aligner->execute($authors, $phrase, 'firstName', 'lastName');
    }

    private function getCriteria(string $keyword): Criteria
    {
        return new Criteria(
            Filters::from(
                Disjunction::fromFilters(
                    Filter::from(
                        FilterField::from(AuthorFirstName::class),
                        FilterOperator::Contains,
                        FilterValue::from($keyword)
                    ),
                    Filter::from(
                        FilterField::from(AuthorLastName::class),
                        FilterOperator::Contains,
                        FilterValue::from($keyword)
                    )
                )
            ),
            Order::none(),
            0,
            self::MAX_MATCHES_PER_KEYWORD
        );
    }
}
