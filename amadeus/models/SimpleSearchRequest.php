<?php

namespace Amadeus\models;

use Amadeus\exceptions\ValidationException;
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

    private $_limit = 20;

    /**
     * Create request.
     *
     * @param DateTime $date        Flight date
     * @param string   $origin      Origin IATA
     * @param string   $destination Destination IATA
     * @param int      $adults      Number of adults
     * @param int      $children    Number of children
     * @param int      $infants     Number of infants
     * @param DateTime $dateReturn  Date of return flight
     * @param string   $currency    Currency of prices
     * @param string   $cabin       Type of cabin
     * @param int      $limit       Number of tickets
     *
     * @throws ValidationException
     */
    public function __construct(DateTime $date, $origin, $destination, $adults, $children = 0, $infants = 0, DateTime $dateReturn = null, $currency = 'USD', $cabin = "Y", $limit = 20)
    {
        //Create validators
        $iataValidator = v::alnum()->noWhitespace()->length(3, 4);
        $dateValidator = v::date()->between(new DateTime(), null, true);
        $returnDateValidator = v::oneOf(v::nullValue(), v::date()->between($date, null, true));

        //Validate dates
        if (!$dateValidator->validate($date)) {
            throw new ValidationException("Please specify flight date");
        }
        if (!$returnDateValidator->validate($dateReturn)) {
            throw new ValidationException("Return date should be null or later than today");
        }

        //Validate iatas
        if (!$iataValidator->validate($origin)) {
            throw new ValidationException("Origin should be 3-4 char IATA code");
        }
        if (!$iataValidator->validate($destination)) {
            throw new ValidationException("Destination should be 3-4 char IATA code");
        }

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
        $this->_limit = $limit;
    }

    /**
     * Number of adults.
     *
     * @return int
     */
    public function getAdults()
    {
        return $this->_adults;
    }

    /**
     * Number of children.
     *
     * @return int
     */
    public function getChildren()
    {
        return $this->_children + (($this->_adults < $this->_infants) ? ($this->_infants - $this->_adults) : 0);
    }

    /**
     * Number of infants.
     *
     * @return int
     */
    public function getInfants()
    {
        return ($this->_adults >= $this->_infants) ? $this->_infants : $this->_adults;
    }

    /**
     * Number of occupied seats.
     *
     * @return int
     */
    public function getSeats()
    {
        return max($this->getAdults(), $this->getInfants()) + $this->getChildren();
    }

    /**
     * Date of flight.
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->_date;
    }

    /**
     * Origin IATA.
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->_origin;
    }

    /**
     * Destination IATA.
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
     * Date of return flight or null if one-way.
     *
     * @return DateTime|null
     */
    public function getDateReturn()
    {
        return $this->_dateReturn;
    }

    /**
     * One way flight request.
     *
     * @return bool
     */
    public function isOneWay()
    {
        return $this->getDateReturn() == null;
    }

    /**
     * Currency of price request.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * Type of cabin, Y-economy, C-business.
     *
     * @return string
     */
    public function getCabin()
    {
        return $this->_cabin;
    }

    /**
     * Number of tickets to fetch.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * @param int $adults
     */
    public function setAdults($adults)
    {
        $this->_adults = $adults;
    }

    /**
     * @param int $children
     */
    public function setChildren($children)
    {
        $this->_children = $children;
    }

    /**
     * @param int $infants
     */
    public function setInfants($infants)
    {
        $this->_infants = $infants;
    }
}
