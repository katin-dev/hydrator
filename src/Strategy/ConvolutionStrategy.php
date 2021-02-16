<?php

namespace App\Strategy;

use App\HydrationRule;
use App\Hydrator;

class ConvolutionStrategy implements \App\StrategyInterface
{
    private Hydrator $hydrator;

    public function __construct(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function hydrate(array $row, HydrationRule $rule)
    {
        return $this->hydrator->hydrate($rule->getClassName(), $row, $rule->getRules());
    }

    public function extract($value, array $row, HydrationRule $rule): array
    {
        if ($value !== null) {
            $row = array_merge($row, $this->hydrator->extract($value, $rule->getRules()));
        }

        return $row;
    }
}