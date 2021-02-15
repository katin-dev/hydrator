<?php

namespace App;

use Doctrine\Instantiator\InstantiatorInterface;
use ReflectionClass;
use ReflectionProperty;
use Test\Product;

class Hydrator
{
    /**
     * Simple in-memory array cache of ReflectionProperties used.
     *
     * @var ReflectionProperty[][]
     */
    protected static $reflProperties = [];

    /**
     * @var InstantiatorInterface
     */
    private $instantiator;

    public function __construct(InstantiatorInterface $instantiator)
    {
        $this->instantiator = $instantiator;
    }

    public function hydrate(array $row, string $className, array $rules)
    {
        $object = $this->instantiator->instantiate($className);

        $objectProperties = self::getReflProperties($object);
        foreach ($objectProperties as $property) {
            /** @var HydrationRule $rule */
            $rule = $rules[$property->getName()] ?? new HydrationRule(HydrationRule::TYPE_DEFAULT, $property->getName(), $property->getName());

            switch ($rule->getType()) {
                case HydrationRule::TYPE_CONVOLUTION:
                    $propertyObject = $this->hydrate($row, $rule->getClassName(), $rule->getRules());
                    $property->setValue($object, $propertyObject);
                    break;

                case HydrationRule::TYPE_JSON:
                    $value = $row[$rule->getColumnName()] ?? null;
                    $valueHydrated = null;
                    $jsonData = $value ? json_decode($value, true) : null;
                    if ($rule->isCollection()) {
                        foreach ($jsonData as $jsonDataRow) {
                            $valueHydrated[] = $this->hydrate($jsonDataRow, $rule->getClassName(), $rule->getRules());
                        }
                    } else {
                        $valueHydrated[] = $this->hydrate($jsonData, $rule->getColumnName(), $rule->getRules());
                    }

                    $property->setValue($object, $valueHydrated);
                    break;

                case HydrationRule::TYPE_INT:
                    $value = isset($row[$rule->getColumnName()]) ? (int) $row[$rule->getColumnName()] : null;
                    $property->setValue($object, $value);
                    break;

                case HydrationRule::TYPE_STRING:
                case HydrationRule::TYPE_DEFAULT:
                default:
                    $property->setValue($object, $row[$rule->getColumnName()] ?? null);
                    break;
            }
        }

        return $object;
    }

    public function extract(object $object, array $rules = []) : array
    {
        $row = [];
        $objectProperties = self::getReflProperties($object);
        foreach ($objectProperties as $property) {
            /** @var HydrationRule $rule */
            $rule = $rules[$property->getName()] ?? new HydrationRule(HydrationRule::TYPE_DEFAULT, $property->getName(), $property->getName());

            switch ($rule->getType()) {
                case HydrationRule::TYPE_CONVOLUTION:
                    $value = $property->getValue($object);
                    if ($value !== null) {
                        $row = array_merge($row, $this->extract($value, $rule->getRules()));
                    }
                    break;
                case HydrationRule::TYPE_JSON:
                    $value = $property->getValue($object);
                    $valueExtracted = null;
                    if ($rule->isCollection()) {
                        foreach ($value as $valueItem) {
                            $valueExtracted[] = $this->extract($valueItem, $rule->getRules());
                        }
                    } else {
                        $valueExtracted = $this->hydrate($value, $rule->getRules());
                    }

                    $row[$rule->getColumnName()] = json_encode($valueExtracted, JSON_UNESCAPED_UNICODE);
                    break;
                case HydrationRule::TYPE_DEFAULT:
                case HydrationRule::TYPE_STRING:
                case HydrationRule::TYPE_INT:
                default:
                    $row[$rule->getColumnName()] = $property->getValue($object);
                    break;
            }
        }

        return $row;
    }

    /**
     * Get a reflection properties from in-memory cache and lazy-load if
     * class has not been loaded.
     *
     * @return ReflectionProperty[]
     */
    protected static function getReflProperties(object $input) : array
    {
        $class = get_class($input);

        if (isset(static::$reflProperties[$class])) {
            return static::$reflProperties[$class];
        }

        static::$reflProperties[$class] = [];
        $reflClass = new ReflectionClass($class);
        $reflProperties = $reflClass->getProperties();

        foreach ($reflProperties as $property) {
            $property->setAccessible(true);
            static::$reflProperties[$class][$property->getName()] = $property;
        }

        return static::$reflProperties[$class];
    }
}