<?php

namespace App\Strategy;

use App\HydrationRule;
use App\StrategyInterface;

class DefaultStrategy implements StrategyInterface
{
    public function hydrate(array $row, HydrationRule $rule)
    {
        $value = $row[$rule->getColumnName()] ?? null;
        if (is_null($value)) return null;

        switch ($rule->getType()) {
            case HydrationRule::TYPE_INT:
                $value = intval($value);
                break;
            case HydrationRule::TYPE_BOOL:
                $value = boolval($value);
                break;
            case HydrationRule::TYPE_STRING:
                $value = (string) $value;
                break;
            case HydrationRule::TYPE_FLOAT:
                $value = floatval($value);
                break;
            case HydrationRule::TYPE_DEFAULT:
                break;
        }

        return $value;
    }

    public function extract($value, array $row, HydrationRule $rule): array
    {
        $row[$rule->getColumnName()] = $value;
        return $row;
    }
}