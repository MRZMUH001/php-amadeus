<?php

namespace amadeus\models;

class SegmentDetails
{
    /** @var string */
    private $_classOfService;

    /**
     * @var BagAllowance
     */
    private $_baggageAllowance;

    public function __construct($classOfService, $baggageAllowance)
    {
        $this->_classOfService = $classOfService;
        $this->_baggageAllowance = $baggageAllowance;
    }

    /**
     * @return string
     */
    public function getClassOfService()
    {
        return $this->_classOfService;
    }

    /**
     * @return BagAllowance
     */
    public function getBaggageAllowance()
    {
        return $this->_baggageAllowance;
    }
}
