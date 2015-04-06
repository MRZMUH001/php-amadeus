<?php

namespace Amadeus\models;


use common\models\Price;
use SebastianBergmann\Money\Money;

class AgentCommissions
{

    /** @var  float */
    private $_commissionAdult;

    /** @var  float */
    private $_commissionChild;

    /** @var  float */
    private $_commissionInfant;

    /**
     * AgentCommissions constructor.
     * @param float $commissionAdult
     * @param float $commissionChild
     * @param float $commissionInfant
     */
    public function __construct($commissionAdult, $commissionChild, $commissionInfant)
    {
        $this->_commissionAdult = $commissionAdult;
        $this->_commissionChild = $commissionChild;
        $this->_commissionInfant = $commissionInfant;
    }

    /**
     * @return float
     */
    public function getCommissionAdult()
    {
        return $this->_commissionAdult;
    }

    /**
     * @return float
     */
    public function getCommissionChild()
    {
        return $this->_commissionChild;
    }

    /**
     * @return float
     */
    public function getCommissionInfant()
    {
        return $this->_commissionInfant;
    }

    /**
     * Applies commission to price
     * @param Price $price
     * @param SimpleSearchRequest $searchRequest
     * @return Money
     */
    public function apply(&$price, SimpleSearchRequest $searchRequest)
    {
        $fare = $price->getFare();
        $commission = new Money(0, $fare->getCurrency());
        $commission->add($fare->multiply($this->getCommissionAdult()));

        //TODO: Calculate properly

        $price->setCommission($commission);
    }

}