<?php

namespace Amadeus;

use Amadeus\Methods\FareRulesTrait;
use Amadeus\Methods\InformativePricingWithoutPnrTrait;
use Amadeus\Methods\PnrAddMultiElementsFinalTrait;
use Amadeus\Methods\PnrAddMultiElementsTrait;
use Amadeus\Methods\PricePnrWithBookingClassTrait;
use Amadeus\Methods\SearchTicketsMethodTrait;
use Amadeus\Methods\SellFromRecommendationTrait;
use Amadeus\Methods\TicketCreateTrait;
use Amadeus\models\OrderFlow;
use Amadeus\models\TicketDetails;
use Monolog\Logger;

abstract class Client
{
    //Tickets search
    use SearchTicketsMethodTrait;

    //Sell from recommendation
    use SellFromRecommendationTrait;

    //Get price details
    use PricePnrWithBookingClassTrait;

    use InformativePricingWithoutPnrTrait;

    //Get fare rules
    use FareRulesTrait;

    //Add passengers
    use PnrAddMultiElementsTrait;

    use PnrAddMultiElementsFinalTrait;

    //Create ticket
    use TicketCreateTrait;

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
     * @var \Monolog\Logger
     */
    private $_logger;

    /**
     * Constructor
     *
     * @param string $env
     * @param bool $debug Echo debug information
     */
    public function __construct($env = 'prod', $debug = true)
    {
        $path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . 'prod' . DIRECTORY_SEPARATOR . 'AmadeusWebServices.wsdl';

        //Create logger
        $this->_logger = new Logger('main');

        // Instantiate the Amadeus class (Debug enabled)
        $this->_ws = new InnerClient($path, $env, $this->_logger);
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
        if (strlen($time) == 2) {
            $hours = substr($time, 0, 1);
            $minutes = substr($time, 1, 1);
        } else {
            $minutes = substr($time, -2);
            $hours = substr($time, 0, strlen($time) - 2);
        }

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
     * @param OrderFlow $orderFlow
     * @return OrderFlow
     * @throws exceptions\UnableToSellException
     */
    public function prepareBooking(OrderFlow $orderFlow)
    {
        //Check bookingability + add segment details
        $orderFlow = $this->sellFromRecommendation($orderFlow);

        //Who knows what for :)
        $this->pricePnrWithBookingClass($orderFlow);

        //Get proper fares
        $orderFlow = $this->informativePricingWithoutPnr($orderFlow);

        //Set fare rules
        $orderFlow->setRules($this->getFareRules());

        $this->closeSession();

        return $orderFlow;
    }

    /**
     * Create PNR
     *
     * @param OrderFlow $orderFlow
     * @return array
     * @throws exceptions\UnableToSellException
     */
    public function createPnr(OrderFlow $orderFlow)
    {
        //Check bookingability + add segment details
        $orderFlow = $this->sellFromRecommendation($orderFlow);

        //Add passenger details
        //TODO: Check for errors
        $this->pnrAddMultiElements($orderFlow);

        $orderFlow = $this->pricePnrWithBookingClass($orderFlow);

        $orderFlow->setPnr($this->pnrAddMultiElementsFinal());

        $this->closeSession();

        return $orderFlow;
    }

    public function ticket(TicketDetails $ticketDetails, $currency)
    {
        //$this->pricePnrWithBookingClass($ticketDetails, $currency);
        //$this->ticketCreate($types);
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

}