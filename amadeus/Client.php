<?php

namespace Amadeus;

use Amadeus\Methods\FareRulesTrait;
use Amadeus\Methods\InformativePricingWithoutPnrTrait;
use Amadeus\Methods\PricePnrWithBookingClassTrait;
use Amadeus\Methods\SearchTicketsMethodTrait;
use Amadeus\Methods\SellFromRecommendationTrait;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketPrice;

class Client
{
    //Tickets search
    use SearchTicketsMethodTrait;

    //Sell from recommendation
    use SellFromRecommendationTrait;

    //Get price details
    use PricePnrWithBookingClassTrait;

    use InformativePricingWithoutPnrTrait;

    use FareRulesTrait;

    /**
     * Is session open
     * @var bool
     */
    private $_isSessionOpen = false;

    /**
     * Amadeus Soap client
     * @var InnerClient
     */
    private $_ws = null;

    /**
     * Constructor
     *
     * @param string $env
     * @param bool $debug Echo debug information
     */
    public function __construct($env = 'prod', $debug = true)
    {
        $path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . $env . DIRECTORY_SEPARATOR . 'AmadeusWebServices.wsdl';
        // Instantiate the Amadeus class (Debug enabled)
        $this->_ws = new InnerClient($path, $env, $debug);
    }

    /**
     * @return InnerClient
     */
    public function getClient()
    {
        return $this->_ws;
    }

    /**
     * Open new amadeus session
     * @param string $officeId
     * @param string $originator
     * @param string $organization
     * @param string $password
     */
    protected function openSession($officeId, $originator, $organization, $password)
    {
        $this->_ws->securityAuthenticate($officeId, $originator, base64_encode($password), strlen($password), $organization);
        $this->_isSessionOpen = true;
    }

    /**
     * Used to iterate std classed
     * Will return multiple subobjects for iterable std and one if non-iterable
     * @param $std
     * @return \Generator
     */
    protected function iterateStd($std)
    {
        if (is_array($std)) {
            foreach ($std as $obj)
                yield $obj;
        } else
            yield $std;
    }

    /**
     * Close amadeus session
     */
    protected function closeSession()
    {
        $this->_ws->securitySignout();
        $this->_isSessionOpen = false;
    }

    /**
     * Convert amadeus date format (010515) to DateTime
     * @param string $date
     * @return \DateTime date
     */
    protected function convertAmadeusDate($date)
    {
        return \DateTime::createFromFormat('dmy', $date);
    }

    /**
     * Convert amadeus time format (2240) to 22:40
     * @param string $time
     * @return string
     */
    protected function convertAmadeusTime($time)
    {
        $minutes = substr($time, -2);
        $hours = substr($time, 0, strlen($time) - 2);

        return $hours . ":" . $minutes;
    }

    /**
     * Convert from 0130 to 90 minutes
     * @param string $duration
     * @return int minutes
     */
    protected function convertAmadeusDurationToMinutes($duration)
    {
        return intval($duration / 100) * 60 + $duration % 100;
    }

    /**
     * Prepare everything for booking page
     *
     * @param TicketPrice $ticketPrice
     * @param SimpleSearchRequest $request
     * @return models\TicketDetails
     * @throws \amadeus\exceptions\UnableToSellException
     */
    public function prepareBooking(TicketPrice $ticketPrice, $request)
    {
        $ticketDetails = $this->sellFromRecommendation($ticketPrice, $request->getSeats());
        $ticketDetails = $this->pricePnrWithBookingClass($ticketDetails, $request->getCurrency());
        $this->informativePricingWithoutPnr($ticketPrice, $request);
        $ticketDetails->setRules($this->getFareRules());

        return $ticketDetails;
    }

}