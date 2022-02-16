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

final class AuthorMatcher
{

    public function __construct(private AuthorRepository $authorRepository)
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

        return $this->orderBySimilarity($phrase, $authors);
    }

    private function getCriteria($keyword): Criteria
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

    private function orderBySimilarity($keyword, array &$authors): array
    {

        usort($authors, function (Author $a, Author $b) use ($keyword) {

            similar_text($keyword, $a->name()->value(), $percentA);
            similar_text($keyword, $b->name()->value(), $percentB);
            return $percentA === $percentB ? 0 : ($percentA > $percentB ? -1 : 1);
        });

        return $authors;
    }
}