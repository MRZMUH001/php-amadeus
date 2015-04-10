<?php

namespace Amadeus;

use Amadeus\exceptions\UnableToSellException;
use Amadeus\models\AgentCommissions;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\OrderFlow;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\requests\Air_SellFromRecommendationRequest;
use Amadeus\requests\Fare_CheckRulesRequest;
use Amadeus\requests\Fare_InformativePricingWithoutPNRRequest;
use Amadeus\requests\Fare_MasterPricerTravelBoardSearchRequest;
use Amadeus\requests\Fare_PricePNRWithBookingClassRequest;
use Amadeus\requests\PNR_AddMultiElementsRequest;
use Amadeus\requests\PNR_RetrieveRequest;
use Amadeus\requests\Ticket_CreateTSTFromPricingRequest;
use Monolog\Logger;

abstract class Client
{
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
     * @param string $validatingCarrier
     * @param SimpleSearchRequest $searchRequest
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
     * @param bool $debug Echo debug information
     */
    public function __construct($env = 'prod', $debug = true)
    {
        $path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . 'AmadeusWebServices.wsdl';

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
     * Open new Amadeus session.
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
     * Close Amadeus session.
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
     * @return array
     * @throws UnableToSellException
     * @throws \Exception
     */
    public function createPnr(OrderFlow $orderFlow)
    {
        //Sell from recommendation - Check bookingability + add segment details
        $sellFromRecommendationReply = Air_SellFromRecommendationRequest::createFromOrderFlow($orderFlow)->send($this);

        if (!$sellFromRecommendationReply->isSuccess()) {
            throw new UnableToSellException("Seats availability not confirmed");
        }

        $sellFromRecommendationReply->copyDataToOrderFlow($orderFlow);

        //Add passenger details
        $pnrAddMultiElementsRequest = PNR_AddMultiElementsRequest::createFromOrderFlow($orderFlow);
        $pnrAddMultiElementsRequest->setSendPaymentData(false);
        $pnrAddMultiElementsReply = $pnrAddMultiElementsRequest->sendPassengers($this);
        if (count($pnrAddMultiElementsReply->getErrors()) > 0 || $pnrAddMultiElementsReply->isSuccessful())
            throw new \Exception("PNR Creation errors: " . join(',', $pnrAddMultiElementsReply->getErrors()));

        $pnrAddMultiElementsReply->copyDataToOrderFlow($orderFlow);

        //Set commissions
        $passengers = $pnrAddMultiElementsReply->getPassengerNumbers();
        $passengerCommissions = [];

        //Try to find passenger type
        foreach ($passengers as $number => $passenger) {
            $passengerType = null;
            foreach ($orderFlow->getPassengers()->getPassengers() as $p) {
                if (
                    strtoupper($p->getFirstNameWithCode()) == $passenger['first'] &&
                    strtoupper($p->getLastName()) == $passenger['last']
                ) {
                    $passengerType = $p->getType();
                    break;
                }
            }

            $commission = null;
            if ($passengerType == 'A')
                $commission = $orderFlow->getCommissions()->getCommissionAdult();
            if ($passengerType == 'C')
                $commission = $orderFlow->getCommissions()->getCommissionChild();
            if ($passengerType == 'I')
                $commission = $orderFlow->getCommissions()->getCommissionInfant();

            if ($commission === null)
                throw new \Exception("Unable to find passenger type");

            $passengerCommissions[$number] = $commission;
        }

        //Send commissions to Amadeus
        $pnrAddMultiElementsRequest = new PNR_AddMultiElementsRequest();
        $pnrAddMultiElementsRequest->setCommissionPerPassenger($passengerCommissions);
        $pnrAddMultiElementsReplyFinal = $pnrAddMultiElementsRequest->sendCommissions($this);
        $pnrAddMultiElementsReplyFinal->copyDataToOrderFlow($orderFlow);
        if (count($pnrAddMultiElementsReply->getErrors()) > 0 || $pnrAddMultiElementsReply->isSuccessful())
            throw new \Exception("Commission send errors: " . join(',', $pnrAddMultiElementsReply->getErrors()));

        //Price PNR
        $pricePnrWithBookingClassRequest = new Fare_PricePNRWithBookingClassRequest();
        $pricePnrWithBookingClassRequest->setCurrency($orderFlow->getSearchRequest()->getCurrency());
        $pricePnrWithBookingClassReply = $pricePnrWithBookingClassRequest->send($this);
        $fares = $pricePnrWithBookingClassReply->getFares();

        //Save last ticketing date to orderflow
        $bookingClass = $orderFlow->getSegments()->getFirstSegment()->getBookingClass();
        $orderFlow->setLastTktDate($fares[$bookingClass]->getLastTktDate());

        //Get PNR Number
        $pnrAddMultiElementsRequest = new PNR_AddMultiElementsRequest();
        $pnrAddMultiElementsReplyFinal = $pnrAddMultiElementsRequest->sendClosePnr($this);
        $pnrAddMultiElementsReplyFinal->copyDataToOrderFlow($orderFlow);

        $this->closeSession();

        return $orderFlow;
    }

    /**
     * Ticket PNR
     *
     * @param OrderFlow $orderFlow
     * @throws \Exception
     */
    public function ticket(OrderFlow &$orderFlow)
    {
        if ($orderFlow->getPnr() == null)
            throw new \Exception("PNR not created in orderflow");

        //Retrieve PNR
        $retrievePnrRequest = new PNR_RetrieveRequest();
        $retrievePnrRequest->setPnrNumber($orderFlow->getPnr());
        $retrievePnrReply = $retrievePnrRequest->send($this);

        //Price PNR
        $pricePnrWithBookingClassRequest = new Fare_PricePNRWithBookingClassRequest();
        $pricePnrWithBookingClassRequest->setCurrency($orderFlow->getSearchRequest()->getCurrency());
        $pricePnrWithBookingClassReply = $pricePnrWithBookingClassRequest->send($this);
        $fares = $pricePnrWithBookingClassReply->getFares();

        //Issue tickets
        $request = new Ticket_CreateTSTFromPricingRequest();
        $request->setFaresCount(count($fares));
        $reply = $request->send($this);
        if (!$reply->isSuccessful())
            throw new \Exception("Ticketing problem: " . $reply->getError());
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->_logger;
    }
}
