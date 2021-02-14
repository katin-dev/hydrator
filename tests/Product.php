<?php

namespace Test;

class Product
{
    private $id;
    private $name;
    private $price;
    private array $actions = [];

    /**
     * Product constructor.
     * @param $id
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }
}