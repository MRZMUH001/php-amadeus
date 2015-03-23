<?php

namespace Amadeus\models;

use DateTime;
use Respect\Validation\Validator as v;

class SimpleSearchRequest
{
    private $_adults = 0;
    private $_children = 0;
    private $_infants = 0;

    private $_date;

    private $_origin;
    private $_destination;

    private $_dateReturn = null;

    private $_currency = 'USD';

    private $_cabin = 'Y';

    /**
     * Create request
     * @param DateTime $date Flight date
     * @param string $origin Origin IATA
     * @param string $destination Destination IATA
     * @param int $adults Number of adults
     * @param int $children Number of children
     * @param int $infants Number of infants
     * @param DateTime $dateReturn Date of return flight
     * @param string $currency Currency of prices
     * @param string $cabin Type of cabin
     * @throws \ValidationException
     */
    function __construct(DateTime $date, $origin, $destination, $adults, $children = 0, $infants = 0, DateTime $dateReturn = null, $currency = 'USD', $cabin = "Y")
    {
        //Create validators
        $iataValidator = v::alnum()->noWhitespace()->length(3, 4);
        $dateValidator = v::date()->between(new DateTime(), null, true);
        $returnDateValidator = v::oneOf(v::nullValue(), v::date()->between($date, null, true));

        //Validate dates
        if (!$dateValidator->validate($date))
            throw new \ValidationException("Please specify flight date");
        if (!$returnDateValidator->validate($dateReturn))
            throw new \ValidationException("Return date should be null or later than today");

        //Validate iatas
        if (!$iataValidator->validate($origin))
            throw new \ValidationException("Origin should be 3-4 char IATA code");
        if (!$iataValidator->validate($destination))
            throw new \ValidationException("Destination should be 3-4 char IATA code");

        //TODO: Add more validation
        $this->_adults = $adults;
        $this->_children = $children;
        $this->_infants = $infants;
        $this->_date = $date;
        $this->_origin = $origin;
        $this->_destination = $destination;
        $this->_dateReturn = $dateReturn;
        $this->_cabin = $cabin;
        $this->_currency = $currency;
    }

    /**
     * Number of adults
     * @return int
     */
    public function getAdults()
    {
        return $this->_adults;
    }

    /**
     * Number of children
     * @return int
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Number of infants
     * @return int
     */
    public function getInfants()
    {
        return $this->_infants;
    }

    /**
     * Date of flight
     * @return DateTime
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Origin IATA
     * @return string
     */
    public function getOrigin()
    {
        return $this->_origin;
    }

    /**
     * Destination IATA
     * @return string
     */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
     * Date of return flight or null if one-way
     * @return DateTime|null
     */
    public function getDateReturn()
    {
        return $this->_dateReturn;
    }

    /**
     * One way flight request
     * @return bool
     */
    public function isOneWay()
    {
        return $this->getDateReturn() == null;
    }

    /**
     * Currency of price request
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Type of cabin, Y-economy, C-business
     * @return string
     */
    public function getCabin()
    {
        return $this->_cabin;
    }

}