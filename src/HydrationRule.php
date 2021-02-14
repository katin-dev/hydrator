<?php

namespace App;

class HydrationRule
{
    const TYPE_ASIS = 'as_is';
    const TYPE_CONVOLUTION = 'convolution';
    const TYPE_JSON = 'json';

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
}