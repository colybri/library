<?php

declare(strict_types=1);

namespace Colybri\Library\Domain\Service\Shared;

class SimilarityAligner
{
    public function execute(array $models, string $keyword, string ...$attributes)
    {
        usort($models, function ($a, $b) use ($keyword, $attributes) {

            $textA = $textB = "";
            foreach ($attributes as $attribute) {
                $textA .= $a->{$attribute}()->value() . " ";
                $textB .= $b->{$attribute}()->value() . " ";
            }

            similar_text($keyword, $textA, $percentA);
            similar_text($keyword, $textB, $percentB);
            return $percentA === $percentB ? 0 : ($percentA > $percentB ? -1 : 1);
        });

        return $models;
    }
}
