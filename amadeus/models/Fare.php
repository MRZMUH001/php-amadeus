<?php

namespace Amadeus\models;

use SebastianBergmann\Money\Money;

class Fare
{

    /** @var  string */
    private $_class;

    /** @var  string */
    private $_validatingCarrier;


    /** @var  BagAllowance */
    private $_bagAllowance;

    /** @var  int */
    private $_peopleCount;

    /** @var  Money[] */
    private $_fares;

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * @return string
     */
    public function getValidatingCarrier()
    {
        return $this->_validatingCarrier;
    }

    /**
     * @param string $validatingCarrier
     */
    public function setValidatingCarrier($validatingCarrier)
    {
        $this->_validatingCarrier = $validatingCarrier;
    }

    /**
     * @return BagAllowance
     */
    public function getBagAllowance()
    {
        return $this->_bagAllowance;
    }

    /**
     * @param BagAllowance $bagAllowance
     */
    public function setBagAllowance($bagAllowance)
    {
        $this->_bagAllowance = $bagAllowance;
    }

    /**
     * @return int
     */
    public function getPeopleCount()
    {
        return $this->_peopleCount;
    }

    /**
     * @param int $peopleCount
     */
    public function setPeopleCount($peopleCount)
    {
        $this->_peopleCount = $peopleCount;
    }

    /**
     * Add fare price
     *
     * @param string $type
     * @param Money $fare
     */
    public function addFareType($type, Money $fare)
    {
        $this->_fares[$type] = $fare;
    }

    /**
     * Total price
     *
     * @return Money
     */
    public function getTotalPrice()
    {
        return $this->_fares[712];
    }

    /**
     * Tax
     *
     * @return Money
     */
    public function getTax()
    {
        return $this->getTotalPrice()->subtract($this->_fares['B']);
    }

}