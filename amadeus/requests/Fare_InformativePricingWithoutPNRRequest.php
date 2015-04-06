<?php

namespace Amadeus\requests;


use Amadeus\Client;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\OrderFlow;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\replies\Fare_InformativePricingWithoutPNRReply;

class Fare_InformativePricingWithoutPNRRequest extends Request
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
     * Create from orderflow
     *
     * @param OrderFlow $orderFlow
     * @return Air_SellFromRecommendationRequest
     */
    public static function createFromOrderFlow(OrderFlow $orderFlow)
    {
        $request = new self;
        $request->setSearchRequest($orderFlow->getSearchRequest());
        $request->setSegments($orderFlow->getSegments());

        return $request;
    }

    /**
     * @param Client $client
     * @return Fare_InformativePricingWithoutPNRReply
     * @throws \Exception
     */
    function send(Client $client)
    {
        if ($this->_segments == null)
            throw new \Exception("Segments not set");

        if ($this->_searchRequest == null)
            throw new \Exception("Search request not set");

        $sr = $this->_searchRequest;

        $params = [];

        //Adults
        $passengerGroups = [];
        $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
            'quantity' => 1,
            'numberOfUnits' => $sr->getAdults()
        ];
        for ($i = 1; $i < $sr->getAdults(); $i++)
            $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i;
        $passengerGroups[] = $passengerGroup;
        $passengerGroup = [];

        //Infants
        if ($sr->getInfants() > 0) {
            $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
                'quantity' => 2,
                'numberOfUnits' => $sr->getInfants()
            ];
            for ($i = 1; $i <= $sr->getInfants(); $i++)
                $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i;
            $passengerGroup['discountPtc'] = [
                'valueQualifier' => 'INF',
                'fareDetails' => ['qualifier' => 766]
            ];
            $passengerGroups[] = $passengerGroup;
            $passengerGroup = [];
        }

        //Children
        if ($sr->getChildren() > 0) {
            $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
                'quantity' => 3,
                'numberOfUnits' => $sr->getChildren()
            ];
            for ($i = 1; $i <= $sr->getChildren(); $i++)
                $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i + $sr->getAdults();
            $passengerGroup['discountPtc'] = [
                'valueQualifier' => 'CH'
            ];
            $passengerGroups[] = $passengerGroup;
        }

        $params['Fare_InformativePricingWithoutPNR']['passengersGroup'] = $passengerGroups;

        //Segments
        $i = 1;
        $segmentsD = [];
        foreach ($this->_segments->getSegments() as $segment) {
            $segmentsD[] = [
                'segmentInformation' => [
                    'flightDate' => [
                        'departureDate' => $segment->getDepartureDate()->format('dmy'),
                        'departureTime' => str_replace(':', '', $segment->getDepartureTime()),
                        'arrivalDate' => $segment->getArrivalDate()->format('dmy'),
                        'arrivalTime' => str_replace(':', '', $segment->getArrivalTime())
                    ],
                    'boardPointDetails' => [
                        'trueLocationId' => $segment->getDepartureIata()
                    ],
                    'offpointDetails' => [
                        'trueLocationId' => $segment->getArrivalIata()
                    ],
                    'companyDetails' => [
                        'marketingCompany' => $segment->getMarketingCarrierIata(),
                        'operatingCompany' => $segment->getOperatingCarrierIata()
                    ],
                    'flightIdentification' => [
                        'flightNumber' => $segment->getFlightNumber(),
                        'bookingClass' => $segment->getBookingClass()
                    ],
                    'flightTypeDetails' => [
                        'flightIndicator' => 0
                    ],
                    'itemNumber' => $i++
                ]
            ];
        }
        $params['Fare_InformativePricingWithoutPNR']['segmentGroup'] = $segmentsD;

        //Pricing
        //Currency override
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][0] = [
            'pricingOptionKey' => [
                'pricingOptionKey' => 'FCO'
            ],
            'currency' => [
                'firstCurrencyDetails' => [
                    'currencyQualifier' => 'FCO',
                    'currencyIsoCode' => $this->_searchRequest->getCurrency()
                ]
            ]
        ];

        //Published fares
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][1]['pricingOptionKey']['pricingOptionKey'] = 'RP';

        //Unifares
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][2]['pricingOptionKey']['pricingOptionKey'] = 'RU';

        return $this->innerSend($client, 'Fare_InformativePricingWithoutPNR', $params, Fare_InformativePricingWithoutPNRReply::class);
    }
}