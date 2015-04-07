<?php

namespace Amadeus\models;

use DateTime;
use SebastianBergmann\Money\Money;

/**
 * Class TicketDetails
 * Contains all ticket data.
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
     * @var FlightSegmentCollection
     */
    private $_segments = [];

    /**
     * @var DateTime
     */
    private $_lastTicketingDate = null;

    /**
     * Fare rules.
     *
     * @var string
     */
    private $_rules = '';

    /**
     * Is published fare.
     *
     * @var bool
     */
    private $_isPublishedFare;

    public function __construct()
    {
        $this->_segments = new FlightSegmentCollection();
    }

    /**
     * Return total tax.
     *
     * @return Money
     */
    public function getTotalTaxes()
    {

        /*
         * $tax = Money::fromString('0', $this->_taxes[0]->getCurrency());
         *
         * foreach ($this->_taxes as $t)
         * $tax = $tax->add($t);
         */

        return $this->_fares['712']->subtract($this->getFare());
    }

    /**
     * Return basic fare.
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

    /**
     * @return string
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * @param string $rules
     */
    public function setRules($rules)
    {
        $this->_rules = $rules;
    }

    /**
     * @param \SebastianBergmann\Money\Money[] $fares
     */
    public function setFares($fares)
    {
        $this->_fares = $fares;
    }

    /**
     * @param \SebastianBergmann\Money\Money[] $taxes
     */
    public function setTaxes($taxes)
    {
        $this->_taxes = $taxes;
    }

    /**
     * @param SegmentDetails[] $segmentDetails
     */
    public function setSegmentDetails($segmentDetails)
    {
        $this->_segmentDetails = $segmentDetails;
    }

    /**
     * @param DateTime $lastTicketingDate
     */
    public function setLastTicketingDate($lastTicketingDate)
    {
        $this->_lastTicketingDate = $lastTicketingDate;
    }

    /**
     * @return FlightSegmentCollection
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * @param FlightSegment $segment
     */
    public function addSegment($segment)
    {
        $this->_segments->addSegment($segment);
    }

    /**
     * @return boolean
     */
    public function isPublishedFare()
    {
        return $this->_isPublishedFare;
    }

    /**
     * @param boolean $isPublishedFare
     */
    public function setIsPublishedFare($isPublishedFare)
    {
        $this->_isPublishedFare = $isPublishedFare;
    }
}
