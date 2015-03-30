<?php

namespace Amadeus\models;

use DateTime;
use SebastianBergmann\Money\Money;

class TicketPrice
{
    /**
     * @var int
     */
    private $_blankCount;

    /**
     * Fare
     * @var Money
     */
    private $_priceFare;

    /**
     * Tax
     * @var Money
     */
    private $_priceTax;

    /**
     * Flight segments
     * @var FlightSegmentCollection
     */
    private $_segments;

    /**
     * Validating carrier IATA
     * @var string
     */
    private $_validatingCarrierIata;

    /**
     * Marketing carrier IATAs
     * @var string[]
     */
    private $_suggestedMarketingCarrierIatas;

    /**
     * Some additional info
     * @var string
     */
    private $_additionalInfo = '';

    private $_cabins;
    private $_bookingClasses;
    private $_availabilities;

    /**
     * @var DateTime|null
     */
    private $_lastTktDate;
    private $_fareBasis;

    /**
     * Published fare?
     * @var boolean
     */
    private $_isPublishedFare;

    /**
     * Create ticket price
     * @param int $blankCount
     * @param Money $priceFare
     * @param Money $priceTax
     * @param $segments
     * @param string $validatingCarrierIata
     * @param string $suggestedMarketingCarrierIatas
     * @param string $additionalInfo
     * @param $cabins
     * @param $bookingClasses
     * @param $availabilities
     * @param DateTime|null $lastTktDate
     * @param $fareBasis
     * @param boolean $isPublishedFare
     */
    function __construct($blankCount, Money $priceFare, Money $priceTax, FlightSegmentCollection $segments, $validatingCarrierIata, $suggestedMarketingCarrierIatas, $additionalInfo, $cabins, $bookingClasses, $availabilities, $lastTktDate, $fareBasis, $isPublishedFare)
    {
        $this->_blankCount = $blankCount;
        $this->_priceFare = $priceFare;
        $this->_priceTax = $priceTax;
        $this->_segments = $segments;
        $this->_validatingCarrierIata = $validatingCarrierIata;
        $this->_suggestedMarketingCarrierIatas = $suggestedMarketingCarrierIatas;
        $this->_additionalInfo = $additionalInfo;
        $this->_cabins = $cabins;
        $this->_bookingClasses = $bookingClasses;
        $this->_availabilities = $availabilities;
        $this->_lastTktDate = $lastTktDate;
        $this->_fareBasis = $fareBasis;
        $this->_isPublishedFare = $isPublishedFare;
    }

    /**
     * @return int
     */
    public function getBlankCount()
    {
        return $this->_blankCount;
    }

    /**
     * @return Money
     */
    public function getPriceFare()
    {
        return $this->_priceFare;
    }

    /**
     * @return Money
     */
    public function getPriceTax()
    {
        return $this->_priceTax;
    }

    /**
     * @return FlightSegmentCollection
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * @return string
     */
    public function getValidatingCarrierIata()
    {
        return $this->_validatingCarrierIata;
    }

    /**
     * @return \string[]
     */
    public function getSuggestedMarketingCarrierIatas()
    {
        return $this->_suggestedMarketingCarrierIatas;
    }

    /**
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->_additionalInfo;
    }

    /**
     * @return mixed
     */
    public function getCabins()
    {
        return $this->_cabins;
    }

    /**
     * @return string[]
     */
    public function getBookingClasses()
    {
        return $this->_bookingClasses;
    }

    /**
     * @return mixed
     */
    public function getAvailabilities()
    {
        return $this->_availabilities;
    }

    /**
     * @return DateTime|null
     */
    public function getLastTktDate()
    {
        return $this->_lastTktDate;
    }

    /**
     * @return mixed
     */
    public function getFareBasis()
    {
        return $this->_fareBasis;
    }

    /**
     * @return boolean
     */
    public function isPublishedFare()
    {
        return $this->_isPublishedFare;
    }

    /**
     * @return Money
     */
    public function getTotalPrice()
    {
        return $this->_priceFare->add($this->_priceTax);
    }

    /**
     * Return provider name
     * @return string
     */
    public function getSource()
    {
        return 'amadeus';
    }

    /**
     * Departure IATA
     * @return string
     */
    public function getDepartureIata()
    {
        return $this->getSegments()->getSegements()[0]->getDepartureIata();
    }

    /**
     * Arrival IATA
     * @return string
     */
    public function getArrivalIata()
    {
        return end($this->getSegments()->getSegements())->getArrivalIata();
    }

}