<?php

namespace App;

interface StrategyInterface
{
    public function hydrate(array $row, HydrationRule $rule);
    public function extract($value, array $row, HydrationRule $rule) : array;
}