<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Shared;

class SimilarityAligner
{
    public function execute(array $models, string $keyword, string $attribute)
    {
        usort($models, function ($a, $b) use ($keyword, $attribute) {
            similar_text($keyword, $a->{$attribute}()->value(), $percentA);
            similar_text($keyword, $b->{$attribute}()->value(), $percentB);
            return $percentA === $percentB ? 0 : ($percentA > $percentB ? -1 : 1);
        });

        return $models;
    }
}
