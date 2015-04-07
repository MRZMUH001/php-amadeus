<?php

namespace amadeus;

use Amadeus\exceptions\UnableToSellException;
use Amadeus\models\AgentCommissions;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\OrderFlow;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketDetails;
use Amadeus\requests\Air_SellFromRecommendationRequest;
use Amadeus\requests\Fare_CheckRulesRequest;
use Amadeus\requests\Fare_InformativePricingWithoutPNRRequest;
use Amadeus\requests\Fare_MasterPricerTravelBoardSearchRequest;
use Amadeus\requests\Fare_PricePNRWithBookingClassRequest;
use Monolog\Logger;

abstract class Client
{
    //Tickets search
    //use SearchTicketsMethodTrait;

    //Sell from recommendation
    //use SellFromRecommendationTrait;

    //Get price details
    //use PricePnrWithBookingClassTrait;

    //use InformativePricingWithoutPnrTrait;

    //Get fare rules
    //use FareRulesTrait;

    //Add passengers
    //use PnrAddMultiElementsTrait;

    //use PnrAddMultiElementsFinalTrait;

    //Create ticket
    //use TicketCreateTrait;

    /**
     * Is session open.
     *
     * @var bool
     */
    private $_isSessionOpen = false;

    /**
     * Amadeus Soap client.
     *
     * @var InnerClient
     */
    private $_ws = null;

    /**
     * @var \Monolog\Logger
     */
    private $_logger;

    /**
     * Should set $price commission.
     *
     * @param FlightSegmentCollection $segments
     * @param string                  $validatingCarrier
     * @param SimpleSearchRequest     $searchRequest
     *
     * @return AgentCommissions
     */
    abstract public function getCommissions(FlightSegmentCollection $segments, $validatingCarrier, SimpleSearchRequest $searchRequest);

    /**
     * @return string
     */
    abstract public function getId();

    /**
     * Constructor.
     *
     * @param string $env
     * @param bool   $debug Echo debug information
     */
    public function __construct($env = 'prod', $debug = true)
    {
        $path = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'wsdl'.DIRECTORY_SEPARATOR.'prod'.DIRECTORY_SEPARATOR.'AmadeusWebServices.wsdl';

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
     * Search for tickets.
     *
     * @param SimpleSearchRequest $searchRequest
     *
     * @return models\Recommendation[]
     *
     * @throws \Exception
     */
    public function searchTickets(SimpleSearchRequest $searchRequest)
    {
        $request = new Fare_MasterPricerTravelBoardSearchRequest();
        $request->setSearchRequest($searchRequest);
        $response = $request->send($this);

        return $response->getRecommendations();
    }

    /**
     * Open new amadeus session.
     *
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
     * Close amadeus session.
     */
    protected function closeSession()
    {
        $this->_ws->securitySignout();
        $this->_isSessionOpen = false;
    }

    /**
     * Prepare everything for booking page.
     *
     * @param OrderFlow $orderFlow
     *
     * @return OrderFlow
     *
     * @throws exceptions\UnableToSellException
     */
    public function prepareBooking(OrderFlow $orderFlow)
    {
        //Sell from recommendation - Check bookingability + add segment details
        $sellFromRecommendationReply = Air_SellFromRecommendationRequest::createFromOrderFlow($orderFlow)->send($this);

        if (!$sellFromRecommendationReply->isSuccess()) {
            throw new UnableToSellException("Seats availability not confirmed");
        }

        $sellFromRecommendationReply->copyDataToOrderFlow($orderFlow);

        //Get bagagge + booking class
        $pricePnrWithBookingClassRequest = new Fare_PricePNRWithBookingClassRequest();
        $pricePnrWithBookingClassRequest->setCurrency($orderFlow->getSearchRequest()->getCurrency());
        $pricePnrWithBookingClassReply = $pricePnrWithBookingClassRequest->send($this);
        $pricePnrWithBookingClassReply->copyDataToOrderFlow($orderFlow);

        //Get proper fares
        $informativePricingWithoutPnrReply = Fare_InformativePricingWithoutPNRRequest::createFromOrderFlow($orderFlow)->send($this);
        $informativePricingWithoutPnrReply->copyDataToOrderFlow($orderFlow);

        //Set fare rules
        $fareCheckRulesReply = (new Fare_CheckRulesRequest())->send($this);
        $orderFlow->setRules($fareCheckRulesReply->getText());

        $this->closeSession();

        return $orderFlow;
    }

    /**
     * Create PNR.
     *
     * @param OrderFlow $orderFlow
     *
     * @return array
     *
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
