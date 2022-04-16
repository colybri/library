<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Author;

use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Domain\FilterField;
use Colybri\Criteria\Domain\FilterOperator;
use Colybri\Criteria\Domain\Filters;
use Colybri\Criteria\Domain\FilterValue;
use Colybri\Criteria\Domain\Order;
use Colybri\Library\Domain\Model\Author\Author;
use Colybri\Library\Domain\Model\Author\AuthorRepository;
use Colybri\Library\Domain\Model\Author\ValueObject\AuthorName;
use Colybri\Library\Domain\Service\Shared\SimilarityAligner;

final class AuthorMatcher
{
    public function __construct(private AuthorRepository $authorRepository, private SimilarityAligner $aligner)
    {
    }

    public function execute($phrase)
    {
        $keywords = explode(' ', $phrase);

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

        return $this->aligner->execute($authors, $phrase, 'name');
    }

    private function getCriteria(string $keyword): Criteria
    {
        return new Criteria(
            Filters::from(
                Filter::from(
                    FilterField::from(AuthorName::class),
                    FilterOperator::Contains,
                    FilterValue::from($keyword)
                )
            ),
            Order::none(),
            null,
            null
        );
    }
}
