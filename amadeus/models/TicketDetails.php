<?php

namespace Amadeus\models;

use DateTime;
use SebastianBergmann\Money\Money;

/**
 * Class TicketDetails
 * Contains all ticket data
 *
 * @package Amadeus\models
 */
class TicketDetails
{
    /**
     * @var Money[]
     */
    private $_fares = [];

    /**
     * @var Money[]
     */
    private $_taxes = [];

    /**
     * @var SegmentDetails[]
     */
    private $_segmentDetails = [];

    /**
     * @var DateTime
     */
    private $_lastTicketingDate = null;

    /**
     * Constructor
     *
     * @param Money[] $fares
     * @param Money[] $taxes
     * @param SegmentDetails[] $segmentDetails
     * @param DateTime $lastTicketingDate
     */
    function __construct($fares, $taxes, $segmentDetails, $lastTicketingDate)
    {
        $this->_fares = $fares;
        $this->_taxes = $taxes;
        $this->_segmentDetails = $segmentDetails;
        $this->_lastTicketingDate = $lastTicketingDate;
    }

    /**
     * Return total tax
     *
     * @return Money
     */
    public function getTotalTaxes()
    {
        $tax = Money::fromString('0', $this->_taxes[0]->getCurrency());

        foreach ($this->_taxes as $t)
            $tax = $tax->add($t);

        return $tax;
    }

    /**
     * Return basic fare
     *
     * @return Money
     */
    public function getFare()
    {
        return $this->_fares['B'];
    }

    /**
     * @return SegmentDetails[]
     */
    public function getSegmentDetails()
    {
        return $this->_segmentDetails;
    }

    /**
     * @return DateTime
     */
    public function getLastTicketingDate()
    {
        return $this->_lastTicketingDate;
    }

}