<?php

namespace Amadeus\models;


class AgentCommission
{

    /**
     * @var bool
     */
    private $_isPercentage;

    /**
     * @var float
     */
    private $_amount;


    /**
     * @var float
     */
    private $_percent;

    /**
     * AgentCommission constructor.
     * @param bool $_isPercentage
     * @param float $_amount
     * @param float $_percent
     */
    public function __construct($_isPercentage, $_amount = null, $_percent = null)
    {
        $this->_isPercentage = $_isPercentage;
        $this->_amount = $_amount;
        $this->_percent = $_percent;
    }

    /**
     * @return boolean
     */
    public function isPercentage()
    {
        return $this->_isPercentage;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->_amount;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->_percent;
    }

}