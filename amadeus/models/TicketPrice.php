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
     * @var FlightSegment[]
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
    private $_fareBases;

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
     * @param $fareBases
     * @param boolean $isPublishedFare
     */
    function __construct($blankCount, Money $priceFare, Money $priceTax, $segments, $validatingCarrierIata, $suggestedMarketingCarrierIatas, $additionalInfo, $cabins, $bookingClasses, $availabilities, $lastTktDate, $fareBases, $isPublishedFare)
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
        $this->_fareBases = $fareBases;
        $this->_isPublishedFare = $isPublishedFare;
    }


}