<?php

namespace App\Strategy;

use App\HydrationRule;
use App\Hydrator;
use App\StrategyInterface;

class JsonStrategy implements StrategyInterface
{
    private Hydrator $hydrator;

    public function __construct(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function hydrate(array $row, HydrationRule $rule)
    {
        $hydrated = null;

        $value = $row[$rule->getColumnName()] ?? null;
        if (is_null($value)) return null;

        $jsonData = json_decode($value, true);

        if ($rule->isCollection()) {
            $hydrated = [];
            if (is_array($jsonData)) {
                foreach ($jsonData as $jsonDataRow) {
                    $hydrated[] = $this->hydrator->hydrate($jsonDataRow, $rule->getClassName(), $rule->getRules());
                }
            }
        } else {
            $hydrated[] = $this->hydrator->hydrate($jsonData, $rule->getColumnName(), $rule->getRules());
        }

        return $hydrated;
    }

    public function extract($value, array $row, HydrationRule $rule): array
    {
        $extracted = null;
        if ($rule->isCollection()) {
            $extracted = [];
            // @TODO traversable
            if (is_array($value)) {
                foreach ($value as $valueItem) {
                    $extracted[] = $this->hydrator->extract($valueItem, $rule->getRules());
                }
            }
        } else {
            $extracted = $this->hydrator->extract($value, $rule->getRules());
        }

        $row[$rule->getColumnName()] = json_encode($extracted, JSON_UNESCAPED_UNICODE);

        return $row;
    }
}