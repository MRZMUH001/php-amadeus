<?php

namespace Amadeus\models;


use DateTime;

class FlightSegment
{

    /**
     * Operating carrier IATA
     * @var string
     */
    private $_operatingCarrierIata;

    /**
     * Marketing carrier IATA
     * @var string
     */
    private $_marketingCarrierIata;

    /**
     * Departure airport IATA
     * @var string
     */
    private $_departureIata;

    /**
     * Departure terminal
     * @var null|string
     */
    private $_departureTerm = null;

    /**
     * Arrival airport IATA
     * @var string
     */
    private $_arrivalIata;

    /**
     * Arrival terminal
     * @var null|string
     */
    private $_arrivalTerm = null;

    /**
     * Flight number
     * @var string
     */
    private $_flightNumber;

    /**
     * Arrival date
     * @var DateTime
     */
    private $_arrivalDate;

    /**
     * Arrival time
     * @var string
     */
    private $_arrivalTime;

    /**
     * Departure date
     * @var DateTime
     */
    private $_departureDate;

    /**
     * Departure time
     * @var string
     */
    private $_departureTime;

    /**
     * Plane type
     * @var null|string
     */
    private $_equipmentTypeIata;

    /**
     * @param string $operatingCarrierIata
     * @param string $marketingCarrierIata
     * @param string $departureIata
     * @param string $arrivalIata
     * @param string $flightNumber
     * @param DateTime $arrivalDate
     * @param string $arrivalTime
     * @param DateTime $departureDate
     * @param string $departureTime
     */
    function __construct($operatingCarrierIata, $marketingCarrierIata, $departureIata, $arrivalIata, $flightNumber, $arrivalDate, $arrivalTime, $departureDate, $departureTime)
    {
        $this->_operatingCarrierIata = $operatingCarrierIata;
        $this->_marketingCarrierIata = $marketingCarrierIata;
        $this->_departureIata = $departureIata;
        $this->_arrivalIata = $arrivalIata;
        $this->_flightNumber = $flightNumber;
        $this->_arrivalDate = $arrivalDate;
        $this->_arrivalTime = $arrivalTime;
        $this->_departureDate = $departureDate;
        $this->_departureTime = $departureTime;
    }

    /**
     * @param null|string $departureTerm
     */
    public function setDepartureTerm($departureTerm)
    {
        $this->_departureTerm = $departureTerm;
    }

    /**
     * @param null|string $arrivalTerm
     */
    public function setArrivalTerm($arrivalTerm)
    {
        $this->_arrivalTerm = $arrivalTerm;
    }

    /**
     * @param null|string $equipmentTypeIata
     */
    public function setEquipmentTypeIata($equipmentTypeIata)
    {
        $this->_equipmentTypeIata = $equipmentTypeIata;
    }

    /**
     * @return string
     */
    public function getOperatingCarrierIata()
    {
        return $this->_operatingCarrierIata;
    }

    /**
     * @return string
     */
    public function getMarketingCarrierIata()
    {
        return $this->_marketingCarrierIata;
    }

    /**
     * @return string
     */
    public function getDepartureIata()
    {
        return $this->_departureIata;
    }

    /**
     * @return null|string
     */
    public function getDepartureTerm()
    {
        return $this->_departureTerm;
    }

    /**
     * @return string
     */
    public function getArrivalIata()
    {
        return $this->_arrivalIata;
    }

    /**
     * @return null|string
     */
    public function getArrivalTerm()
    {
        return $this->_arrivalTerm;
    }

    /**
     * @return string
     */
    public function getFlightNumber()
    {
        return $this->_flightNumber;
    }

    /**
     * @return DateTime
     */
    public function getArrivalDate()
    {
        return $this->_arrivalDate;
    }

    /**
     * @return string
     */
    public function getArrivalTime()
    {
        return $this->_arrivalTime;
    }

    /**
     * @return DateTime
     */
    public function getDepartureDate()
    {
        return $this->_departureDate;
    }

    /**
     * @return string
     */
    public function getDepartureTime()
    {
        return $this->_departureTime;
    }

    /**
     * @return null|string
     */
    public function getEquipmentTypeIata()
    {
        return $this->_equipmentTypeIata;
    }


}