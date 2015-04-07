<?php

namespace Amadeus\models;

use DateTime;

class FlightSegment
{
    /**
     * Operating carrier IATA.
     *
     * @var string
     */
    private $_operatingCarrierIata;

    /**
     * Marketing carrier IATA.
     *
     * @var string
     */
    private $_marketingCarrierIata;

    /**
     * Departure airport IATA.
     *
     * @var string
     */
    private $_departureIata;

    /**
     * Departure terminal.
     *
     * @var null|string
     */
    private $_departureTerm = null;

    /**
     * Arrival airport IATA.
     *
     * @var string
     */
    private $_arrivalIata;

    /**
     * Arrival terminal.
     *
     * @var null|string
     */
    private $_arrivalTerm = null;

    /**
     * Flight number.
     *
     * @var string
     */
    private $_flightNumber;

    /**
     * Arrival date.
     *
     * @var DateTime
     */
    private $_arrivalDate;

    /**
     * Arrival time (H:m).
     *
     * @var string
     */
    private $_arrivalTime;

    /**
     * Departure date.
     *
     * @var DateTime
     */
    private $_departureDate;

    /**
     * Departure time (H:m).
     *
     * @var string
     */
    private $_departureTime;

    /**
     * Plane type.
     *
     * @var null|string
     */
    private $_equipmentTypeIata;

    /**
     * Booking class.
     *
     * @var null
     */
    private $_bookingClass = null;

    /**
     * @var int
     */
    private $_technicalStopsCount;

    /**
     * @var BagAllowance
     */
    private $_bagAllowance;

    /**
     * @param string   $operatingCarrierIata
     * @param string   $marketingCarrierIata
     * @param string   $departureIata
     * @param string   $arrivalIata
     * @param string   $flightNumber
     * @param DateTime $arrivalDate
     * @param string   $arrivalTime
     * @param DateTime $departureDate
     * @param string   $departureTime
     */
    public function __construct($operatingCarrierIata, $marketingCarrierIata, $departureIata, $arrivalIata, $flightNumber, $arrivalDate = null, $arrivalTime = null, $departureDate = null, $departureTime = null)
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
     * Arrival time (H:m).
     *
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
     * Departure time (H:m).
     *
     * @return string
     */
    public function getDepartureTime()
    {
        $time = $this->_departureTime;
        if (strlen($time) < 5) {
            list($hour, $minutes) = explode(':', $time);

            return sprintf('%02d:%02d', $hour, $minutes);
        }

        return $time;
    }

    /**
     * Plane type IATA.
     *
     * @return null|string
     */
    public function getEquipmentTypeIata()
    {
        return $this->_equipmentTypeIata;
    }

    /**
     * "4U:LH" in case 4U operates, just "LH" in case it's not codeshare.
     *
     * @return string
     */
    public function getCarrierPair()
    {
        return implode(':', array_unique([$this->getOperatingCarrierIata(), $this->getMarketingCarrierIata()]));
    }

    /**
     * Return flight number: SU712.
     *
     * @return string
     */
    public function getFullFlightNumber()
    {
        return $this->getCarrierPair().$this->getFlightNumber();
    }

    /**
     * Serialized flight hash.
     *
     * @return string
     */
    public function serialize()
    {
        $params = [
            $this->getFullFlightNumber(),
            $this->getDepartureIata(),
            $this->getArrivalIata(),
            $this->getDepartureDate()->format('dmy'),
        ];

        return implode('_', $params);
    }

    /**
     * Deserialize flight segment.
     *
     * @param string $code
     *
     * @return FlightSegment
     */
    public static function deserialize($code)
    {
        list($flightCode, $departureIata, $arrivalIata, $departureDate) = explode('_', $code);

        $departureDate = DateTime::createFromFormat('dmy', $departureDate);

        $flightNumber = substr($flightCode, -3);
        $carriers = substr($flightCode, 0, strlen($flightCode) - 3);

        if (strpos($carriers, ':') === false) {
            $operatingCarrier = $marketingCarrier = $carriers;
        } else {
            list($operatingCarrier, $marketingCarrier) = explode(':', $carriers);
        }

        return new FlightSegment($operatingCarrier, $marketingCarrier, $departureIata, $arrivalIata, $flightNumber, null, null, $departureDate);
    }

    /**
     * Departure DateTime with timezone.
     *
     * @param callable $timezoneResolver Function resolving IATA to timezone string
     *
     * @return DateTime
     */
    public function getDepartureDateTime($timezoneResolver)
    {
        $date = clone $this->getDepartureDate();
        list($hour, $minute) = explode(':', $this->getDepartureTime());

        $timezone = $timezoneResolver($this->getDepartureIata());
        $date->setTimezone(new \DateTimeZone($timezone));

        $date->setTime($hour, $minute);

        return $date;
    }

    /**
     * Arrival DateTime with timezone.
     *
     * @param callable $timezoneResolver Function resolving IATA to timezone string
     *
     * @return DateTime
     */
    public function getArrivalDateTime($timezoneResolver)
    {
        $date = clone $this->getArrivalDate();
        list($hour, $minute) = explode(':', $this->getArrivalTime());

        $timezone = $timezoneResolver($this->getArrivalIata());
        $date->setTimezone(new \DateTimeZone($timezone));

        $date->setTime($hour, $minute);

        return $date;
    }

    /**
     * Return segment duration in minutes.
     *
     * @param callable $timezoneResolver
     *
     * @return int
     */
    public function getDurationInMinutes($timezoneResolver)
    {
        $diff = $this->getArrivalDateTime($timezoneResolver)->getTimestamp() - $this->getDepartureDateTime($timezoneResolver)->getTimestamp();

        return abs(round($diff / 60));
    }

    /**
     */
    public function getBookingClass()
    {
        return $this->_bookingClass;
    }

    /**
     * @param null $bookingClass
     */
    public function setBookingClass($bookingClass)
    {
        $this->_bookingClass = $bookingClass;
    }

    /**
     * @return int
     */
    public function getTechnicalStopsCount()
    {
        return $this->_technicalStopsCount;
    }

    /**
     * @param int $technicalStopsCount
     */
    public function setTechnicalStopsCount($technicalStopsCount)
    {
        $this->_technicalStopsCount = $technicalStopsCount;
    }

    /**
     * @param string $operatingCarrierIata
     */
    public function setOperatingCarrierIata($operatingCarrierIata)
    {
        $this->_operatingCarrierIata = $operatingCarrierIata;
    }

    /**
     * @param string $marketingCarrierIata
     */
    public function setMarketingCarrierIata($marketingCarrierIata)
    {
        $this->_marketingCarrierIata = $marketingCarrierIata;
    }

    /**
     * @return BagAllowance
     */
    public function getBagAllowance()
    {
        return $this->_bagAllowance;
    }

    /**
     * @param BagAllowance $bagAllowance
     */
    public function setBagAllowance($bagAllowance)
    {
        $this->_bagAllowance = $bagAllowance;
    }
}
