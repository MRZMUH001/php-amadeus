<?php

namespace Amadeus\models;


use SebastianBergmann\Money\Money;

class FaresPerPassengers
{

    /** @var Money */
    private $_adultsFare;

    /** @var Money */
    private $_childrenFare;

    /** @var Money */
    private $_infantsFare;

    /**
     * @param Money $adultsFare
     */
    public function setAdultsFare($adultsFare)
    {
        $this->_adultsFare = $adultsFare;
    }

    /**
     * @param Money $childrenFare
     */
    public function setChildrenFare($childrenFare)
    {
        $this->_childrenFare = $childrenFare;
    }

    /**
     * @param Money $infantsFare
     */
    public function setInfantsFare($infantsFare)
    {
        $this->_infantsFare = $infantsFare;
    }


    /**
     * Calculates commissions
     *
     * @param AgentCommissions $commissions
     * @return Money
     */
    public function calculateCommission(AgentCommissions $commissions)
    {
        $commission = $this->_adultsFare->multiply($commissions->getCommissionAdult());

        if ($this->_childrenFare != null) {
            $commission = $commission->add(
                $this->_childrenFare->multiply($commissions->getCommissionChild())
            );
        }

        if ($this->_infantsFare != null) {
            $commission = $commission->add(
                $this->_infantsFare->multiply($commissions->getCommissionInfant())
            );
        }

        return $commission->multiply(-1);
    }

}