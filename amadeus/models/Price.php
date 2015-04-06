<?php

namespace Amadeus\models;

use SebastianBergmann\Money\Money;

class Price
{

    /** @var Money */
    private $_fare;

    /** @var Money */
    private $_tax;

    /** @var Money */
    private $_commission;

    /** @var Money */
    private $_markup = null;

    /** @var Money */
    private $_fee;

    /**
     * @param Money $fare
     * @param Money $tax
     */
    function __construct($fare, $tax)
    {
        $this->_fare = $fare;
        $this->_tax = $tax;
    }

    /**
     * @return Money
     */
    public function getFare()
    {
        return $this->_fare;
    }

    /**
     * @param Money $fare
     */
    public function setFare($fare)
    {
        $this->_fare = $fare;
    }

    /**
     * @return Money
     */
    public function getTax()
    {
        return $this->_tax;
    }

    /**
     * @param Money $tax
     */
    public function setTax($tax)
    {
        $this->_tax = $tax;
    }

    /**
     * @return Money
     * @throws \Exception
     */
    public function getCommission()
    {
        if ($this->_commission == null)
            throw new \Exception("Commission not set");

        return $this->_commission;
    }

    /**
     * @param Money $commission
     */
    public function setCommission($commission)
    {
        $this->_commission = $commission;
    }

    /**
     * @return Money
     */
    public function getMarkup()
    {
        if ($this->_markup == null)
            return new Money(0, $this->_fare->getCurrency());

        return $this->_markup;
    }

    /**
     * @param Money $markup
     */
    public function setMarkup($markup)
    {
        $this->_markup = $markup;
    }

    /**
     * @return Money
     * @throws \Exception
     */
    public function getFee()
    {
        if ($this->_fee == null)
            throw new \Exception("Payment fee is not set");

        return $this->_fee;
    }

    /**
     * @param Money $fee
     */
    public function setFee($fee)
    {
        $this->_fee = $fee;
    }

    /**
     * Total price
     *
     * @return Money
     * @throws \Exception
     */
    public function getTotalPrice()
    {
        return $this->getTotalPriceWithoutFee()->add($this->getFee());
    }

    /**
     * Total price without fee
     *
     * @return Money
     * @throws \Exception
     */
    public function getTotalPriceWithoutFee()
    {
        return $this->getFare()->add($this->getTax())->add($this->getMarkup())->add($this->getCommission());
    }

}