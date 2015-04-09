<?php

namespace Amadeus\models;

class AgentCommissions
{
    /** @var  float */
    private $_commissionAdult;

    /** @var  float */
    private $_commissionChild;

    /** @var  float */
    private $_commissionInfant;

    /** @var  float */
    private $_additionalCharge = 0;

    /**
     * AgentCommissions constructor.
     *
     * @param float $commissionAdult
     * @param float $commissionChild
     * @param float $commissionInfant
     * @param float|int $additionalCharge
     */
    public function __construct($commissionAdult = null, $commissionChild = null, $commissionInfant = null, $additionalCharge = 0)
    {
        $this->_commissionAdult = $commissionAdult;
        $this->_commissionChild = $commissionChild;
        $this->_commissionInfant = $commissionInfant;
        $this->_additionalCharge = $additionalCharge;
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
     * @return float
     */
    public function getAdditionalCharge()
    {
        return $this->_additionalCharge;
    }

}
