<?php

namespace Test;

class Price
{
    private $value;
    private $currency;

    /**
     * Price constructor.
     * @param $value
     * @param $currency
     */
    public function __construct($value, $currency)
    {
        $this->value    = $value;
        $this->currency = $currency;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}