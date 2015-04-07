<?php

namespace Amadeus\requests;

use Amadeus\Client;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\OrderFlow;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\replies\Air_SellFromRecommendationReply;

class Air_SellFromRecommendationRequest extends Request
{
    /** @var  FlightSegmentCollection */
    private $_segments;

    /** @var  SimpleSearchRequest */
    private $_searchRequest;

    /**
     * @param FlightSegmentCollection $segments
     */
    public function setSegments($segments)
    {
        $this->_segments = $segments;
    }

    /**
     * @param SimpleSearchRequest $searchRequest
     */
    public function setSearchRequest($searchRequest)
    {
        $this->_searchRequest = $searchRequest;
    }

    /**
     * Create from orderflow.
     *
     * @param OrderFlow $orderFlow
     *
     * @return Air_SellFromRecommendationRequest
     */
    public static function createFromOrderFlow(OrderFlow $orderFlow)
    {
        $request = new self();
        $request->setSearchRequest($orderFlow->getSearchRequest());
        $request->setSegments($orderFlow->getSegments());

        return $request;
    }

    /**
     * @param Client $client
     *
     * @return Air_SellFromRecommendationReply
     *
     * @throws \Exception
     */
    public function send(Client $client)
    {
        if ($this->_segments == null) {
            throw new \Exception("Segments not set");
        }

        if ($this->_searchRequest == null) {
            throw new \Exception("Search request not set");
        }

        $s = $this->_segments;

        $params = [];
        $params['messageActionDetails']['messageFunctionDetails']['messageFunction'] = 183;
        $params['messageActionDetails']['messageFunctionDetails']['additionalMessageFunction'] = 'M1';
        $params['itineraryDetails']['originDestinationDetails']['origin'] = $s->getFirstSegment()->getDepartureIata();
        $params['itineraryDetails']['originDestinationDetails']['destination'] = $s->getLastSegment()->getArrivalIata();
        $params['itineraryDetails']['message']['messageFunctionDetails']['messageFunction'] = 183;

        $i = 0;
        foreach ($s->getSegments() as $segment) {
            if ($segment->getBookingClass() == null) {
                throw new \Exception("Segment booking class not set");
            }

            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightDate']['departureDate'] = $segment->getDepartureDate()->format('dmy');
            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['boardPointDetails']['trueLocationId'] = $segment->getDepartureIata();
            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['offpointDetails']['trueLocationId'] = $segment->getArrivalIata();
            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['companyDetails']['marketingCompany'] = $segment->getMarketingCarrierIata();
            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightIdentification']['flightNumber'] = $segment->getFlightNumber();
            $params['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightIdentification']['bookingClass'] = $segment->getBookingClass();
            $params['itineraryDetails']['segmentInformation'][$i]['relatedproductInformation']['quantity'] = $this->_searchRequest->getSeats();
            $params['itineraryDetails']['segmentInformation'][$i]['relatedproductInformation']['statusCode'] = 'NN';
            $i++;
        }

        return $this->innerSend($client, 'Air_SellFromRecommendation', $params, Air_SellFromRecommendationReply::class);
    }
}
