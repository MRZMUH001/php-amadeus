<?php

namespace Amadeus\models;

class BagAllowance
{
    /**
     * Number of measurement units.
     *
     * @var float
     */
    private $_freeAllowance;

    /**
     * N(number of bags)/700.
     *
     * @var string
     */
    private $_quantityCode;

    public function __construct($freeAllowance, $quantityCode)
    {
        $this->_freeAllowance = $freeAllowance;
        $this->_quantityCode = $quantityCode;
    }

    /**
     * @return float
     */
    public function getFreeAllowance()
    {
        return $this->_freeAllowance;
    }

    /**
     * @return string
     */
    public function getQuantityCode()
    {
        return $this->_quantityCode;
    }
}
