<?php

namespace Amadeus\models;


class BagAllowance {

    /**
     * Number of measurement units
     *
     * @var float
     */
    private $_weight;

    /**
     * N - quantity or W - weight
     *
     * @var string
     */
    private $_type;

    /**
     * Measurement unit (usually kilos - K)
     *
     * @var string
     */
    private $_unit;

    /**
     * Number of bags
     *
     * @var int
     */
    private $_quantity;

    function __construct($_weight, $_type, $_unit,$_quantity)
    {
        $this->_weight = $_weight;
        $this->_type = $_type;
        $this->_unit = $_unit;
        $this->_quanity = $_quantity;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->_weight;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->_unit;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->_quantity;
    }

}