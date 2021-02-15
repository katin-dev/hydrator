<?php

namespace App;

class HydrationRule
{
    const TYPE_DEFAULT = 'default',
          TYPE_CONVOLUTION = 'convolution',
          TYPE_JSON = 'json',
          TYPE_INT = 'int',
          TYPE_STRING = 'string',
          TYPE_FLOAT = 'float',
          TYPE_BOOL = 'bool',
          TYPE_DATETIME = 'datetime';

    private string $fieldName;
    private string $columnName;
    private array $options;
    private string $type;

    public function __construct(string $type, string $fieldName, string $columnName, array $options = [])
    {
        $this->fieldName = $fieldName;
        $this->columnName = $columnName;
        $this->options = $options;
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function getClassName()
    {
        return $this->options['className'] ?? 'stdClass';
    }

    public function getRules()
    {
        return $this->options['rules'] ?? [];
    }

    public function isCollection()
    {
        return $this->options['is_collection'] ?? false;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getOptions() : array
    {
        return $this->options;
    }
}