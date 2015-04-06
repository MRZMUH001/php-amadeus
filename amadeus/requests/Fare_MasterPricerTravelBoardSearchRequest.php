<?php

namespace Amadeus\requests;

use Amadeus\InnerClient;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\responses\Fare_MasterPricerTravelBoardSearchResponse;

class Fare_MasterPricerTravelBoardSearchRequest extends Request
{

    /** @var  SimpleSearchRequest */
    private $_searchRequest;

    /**
     * @return SimpleSearchRequest
     */
    public function getSearchRequest()
    {
        return $this->_searchRequest;
    }

    /**
     * @param SimpleSearchRequest $searchRequest
     */
    public function setSearchRequest($searchRequest)
    {
        $this->_searchRequest = $searchRequest;
    }

    /**
     * @param InnerClient $client
     * @return Fare_MasterPricerTravelBoardSearchResponse
     * @throws \Exception
     */
    public function send(InnerClient $client)
    {
        if ($this->_searchRequest == null)
            throw new \Exception("Search request not set");

        $r = $this->_searchRequest;

        $params = [];
        $params['numberOfUnit']['unitNumberDetail'][0]['numberOfUnits'] = $r->getAdults() + $r->getChildren();
        $params['numberOfUnit']['unitNumberDetail'][0]['typeOfUnit'] = 'PX';
        $params['numberOfUnit']['unitNumberDetail'][1]['numberOfUnits'] = $r->getLimit();
        $params['numberOfUnit']['unitNumberDetail'][1]['typeOfUnit'] = 'RC';

        $params['paxReference'][0]['ptc'] = 'ADT';
        for ($i = 1; $i <= $r->getAdults(); $i++)
            $params['paxReference'][0]['traveller'][]['ref'] = $i;

        $j = 1;
        if ($r->getChildren() > 0) {
            $params['paxReference'][$j]['ptc'] = 'CH';
            for ($i = 1; $i <= $r->getChildren(); $i++)
                $params['paxReference'][$j]['traveller'][]['ref'] = $i + $r->getAdults();
            $j++;
        }

        if ($r->getInfants() > 0) {
            $params['paxReference'][$j]['ptc'] = 'INF';
            for ($i = 1; $i <= $r->getInfants(); $i++)
                $params['paxReference'][$j]['traveller'][] = ['ref' => $i, 'infantIndicator' => 1];
        }

        $params['fareOptions']['pricingTickInfo']['pricingTicketing']['priceType'] = [
            # currency conversion override. is it still needed?
            'CUC',
            # (only?) etickets
            'ET',
            # public fares
            'RP',
            # unifare
            'RU',
            # no slice and dice (we don't know how to book it yet)
            'NSD',
            # ticket-ability check
            'TAC'
        ];

        $params['fareOptions']['feeIdDescription']['feeId'] = ['feeType' => 'SORT', 'feeIdNumber' => 'FEE'];

        $params['fareOptions']['conversionRate']['conversionRateDetail'] = ['currency' => $r->getCurrency()];
        $params['travelFlightInfo']['cabinId'] = ['cabinQualifier' => 'MC', 'cabin' => $r->getCabin()];


        $params['itinerary'][0]['requestedSegmentRef']['segRef'] = 1;
        $params['itinerary'][0]['departureLocalization']['depMultiCity']['locationId'] = $r->getOrigin();
        $params['itinerary'][0]['arrivalLocalization']['arrivalMultiCity']['locationId'] = $r->getDestination();
        $params['itinerary'][0]['timeDetails']['firstDateTimeDetail']['date'] = $r->getDate()->format('dmy');

        if ($r->getDateReturn() != null) {
            $params['itinerary'][1]['requestedSegmentRef']['segRef'] = 2;
            $params['itinerary'][1]['departureLocalization']['depMultiCity']['locationId'] = $r->getDestination();
            $params['itinerary'][1]['arrivalLocalization']['arrivalMultiCity']['locationId'] = $r->getOrigin();
            $params['itinerary'][1]['timeDetails']['firstDateTimeDetail']['date'] = $r->getDateReturn()->format('dmy');
        }

        return $this->innerSend($client, 'Fare_MasterPricerTravelBoardSearch', $params, Fare_MasterPricerTravelBoardSearchResponse::class);
    }

}