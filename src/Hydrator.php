<?php

namespace App;

use App\Strategy\ConvolutionStrategy;
use App\Strategy\DateTimeStrategy;
use App\Strategy\DefaultStrategy;
use App\Strategy\JsonStrategy;
use Doctrine\Instantiator\InstantiatorInterface;
use ReflectionClass;
use ReflectionProperty;

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

    /**
     * @param array $row
     * @param string $className
     * @param HydrationRule[] $rules
     * @return object
     * @throws \Doctrine\Instantiator\Exception\ExceptionInterface
     */
    public function hydrate(string $className, array $row, array $rules = [])
    {
        $object = $this->instantiator->instantiate($className);

        $objectProperties = self::getReflProperties($object);
        if (!$rules) {
            $rules = $this->generateDefaultRules($objectProperties);
        }

        $rules = $this->groupRulesByFieldName($rules);

        foreach ($objectProperties as $property) {
            /** @var HydrationRule $rule */
            $rule = $rules[$property->getName()] ?? null;

            if (!$rule) continue;

            $strategy = $this->getStrategy($rule);

            $value = $strategy->hydrate($row, $rule);

            $property->setValue($object, $value);
        }

        return $object;
    }

    public function extract(object $object, array $rules = []) : array
    {
        $row = [];

        $objectProperties = self::getReflProperties($object);

        if (!$rules) {
            $rules = $this->generateDefaultRules($objectProperties);
        }
        $rules = $this->groupRulesByFieldName($rules);

        foreach ($objectProperties as $property) {
            /** @var HydrationRule $rule */
            $rule = $rules[$property->getName()] ?? null;
            if (is_null($rule)) continue;

            $value = $property->getValue($object);

            $strategy  = $this->getStrategy($rule);

            $row = $strategy->extract($value, $row, $rule);
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

    /**
     * @param array $rules
     * @return mixed
     */
    private function groupRulesByFieldName(array $rules)
    {
        $rulesByFieldName = [];
        foreach ($rules as $rule) {
            $rulesByFieldName[$rule->getFieldName()] = $rule;
        }
        return $rulesByFieldName;
    }

    /**
     * @param ReflectionProperty[] $objectProperties
     * @return array
     */
    private function generateDefaultRules(array $objectProperties) : array
    {
        $rules = [];
        foreach ($objectProperties as $property) {
            $rules[] = new HydrationRule(HydrationRule::TYPE_DEFAULT, $property->getName(), $property->getName());
        }
        return $rules;
    }

    private function getStrategy(HydrationRule $rule) : StrategyInterface
    {
        switch ($rule->getType()) {
            case HydrationRule::TYPE_CONVOLUTION:
                return new ConvolutionStrategy($this);
                break;
            case HydrationRule::TYPE_DATETIME:
                return new DateTimeStrategy();
                break;
            case HydrationRule::TYPE_JSON:
                return new JsonStrategy($this);
                break;
            default:
                return new DefaultStrategy();
        }
    }
}