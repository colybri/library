<?php

declare(strict_types=1);

namespace Colybri\Library\Infrastructure\Persistence\Api\Google;

use Colybri\Criteria\Domain\Condition;
use Colybri\Criteria\Domain\ConditionVisitor;
use Colybri\Criteria\Domain\Conjunction;
use Colybri\Criteria\Domain\Criteria;
use Colybri\Criteria\Domain\Disjunction;
use Colybri\Criteria\Domain\Filter;
use Colybri\Criteria\Infrastructure\Adapter\EntityMap;
use Colybri\Library\Infrastructure\Persistence\Api\Google\Exception\NotGrammaticalExpressionAllowed;

class CriteriaGoogleBookAdapter implements ConditionVisitor
{
    private int $countParams;

    public function __construct(private string $path, private EntityMap $entityMap)
    {
        $this->countParams = 0;
    }

    public function build(Criteria $criteria): string
    {

        foreach ($criteria->filters() as $theFilter) {
            $this->buildExpression($theFilter);
        }

        if (null !== $criteria->offset()) {
            $this->path .= '&startIndex='.$criteria->offset();
        }

        if (null !== $criteria->limit()) {
            $this->path .= '&maxResults='.$criteria->offset();
        }

        return $this->path;
    }

    public function visitAnd(Conjunction $statement): void
    {
        foreach ($statement->filters() as $index => $filter) {
            $this->path .= $this->buildExpression($filter);
            if ($index !== array_key_last($statement->filters())) {
                $this->path .= '+';
            }
        }
    }

    public function visitOr(Disjunction $statement): string
    {
        throw new NotGrammaticalExpressionAllowed('Google Book Api dont allow disjunctional conditions request');
    }

    public function visitFilter(Filter $filter): string
    {
        $this->countParams++;

        return $this->mapParameter($filter).':'.$this->mapFieldValue($filter);
    }

    private function buildExpression(Condition $filter)
    {
        return $filter->accept($this);
    }

    private function mapParameter(Filter $filter)
    {
        return  $this->entityMap->map($filter->field()->value());
    }

    private function mapFieldValue(Filter $filter)
    {
        return $filter->value()->value();
    }
}