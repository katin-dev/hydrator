<?php

namespace App\Strategy;

use App\HydrationRule;
use App\StrategyInterface;

class DateTimeStrategy implements StrategyInterface
{
    public function hydrate(array $row, HydrationRule $rule)
    {
        $value = $row[$rule->getColumnName()] ?? null;
        if (is_null($value)) return null;

        $dt = new \DateTime('now');
        $dt->setTimestamp(strtotime($value));

        return $dt;
    }

    public function extract($value, array $row, HydrationRule $rule): array
    {
        if ($value instanceof \DateTimeInterface) {
            $options = $rule->getOptions();
            $format = $options['format'] ?? 'Y-m-d H:i:s';
            $row[$rule->getColumnName()] = $value->format($format);
        }

        return $row;
    }
}