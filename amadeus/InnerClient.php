<?php
/*
 * Amadeus Flight Booking and Search & Booking API Client
 *
 * (c) Krishnaprasad MG <sunspikes@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Amadeus;

use Amadeus\exceptions\AmadeusException;
use Amadeus\models\AgentCommissions;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\Passenger;
use Amadeus\models\PassengerCollection;
use Monolog\Logger;

/**
 * @see https://extranets.us.amadeus.com
 */
class InnerClient
{
    /**
     * The main Amadeus WS namespace.
     *
     * @var string
     */
    const AMD_HEAD_NAMESPACE = 'http://xml.amadeus.com/ws/2009/01/WBS_Session-2.0.xsd';

    /**
     * Response data.
     */
    private $_data = null;

    /**
     * Response headers.
     */
    private $_headers = null;

    /**
     * Hold the client object.
     */
    private $_client = null;

    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @param $wsdl  string   Path to the WSDL file
     * @param $env   string   prod/test
     * @param $logger Logger
     */
    public function __construct($wsdl, $env = 'prod', &$logger)
    {
        $this->_logger = $logger;
        $endpoint = 'https://test.webservices.amadeus.com';
        if ($env == 'prod') {
            $endpoint = 'https://production.webservices.amadeus.com';
        }
        $this->_client = new \SoapClient($wsdl, ['trace' => true, 'location' => $endpoint, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP]);
    }

    /**
     * @return Object
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return Object
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Security_Authenticate
     * Autheticates with Amadeus.
     *
     * @param string $source sourceOffice string
     * @param string $origin originator string
     * @param string $password password binaryData
     * @param integer $passlen length of binaryData
     * @param string $org organizationId string
     *
     * @return Object
     */
    public function securityAuthenticate($source, $origin, $password, $passlen, $org)
    {
        $params = [];
        $params['Security_Authenticate']['userIdentifier']['originIdentification']['sourceOffice'] = $source;
        $params['Security_Authenticate']['userIdentifier']['originatorTypeCode'] = 'U';
        $params['Security_Authenticate']['userIdentifier']['originator'] = $origin;
        $params['Security_Authenticate']['dutyCode']['dutyCodeDetails']['referenceQualifier'] = 'DUT';
        $params['Security_Authenticate']['dutyCode']['dutyCodeDetails']['referenceIdentifier'] = 'SU';
        $params['Security_Authenticate']['systemDetails']['organizationDetails']['organizationId'] = $org;
        $params['Security_Authenticate']['passwordInfo']['dataLength'] = $passlen;
        $params['Security_Authenticate']['passwordInfo']['dataType'] = 'E';
        $params['Security_Authenticate']['passwordInfo']['binaryData'] = $password;

        return $this->soapCall('Security_Authenticate', $params);
    }

    /**
     * Security_SignOut
     * Signs out from Amadeus.
     */
    public function securitySignout()
    {
        $params = [];

        if (isset($this->_headers['Session'])) {
            $params['Security_SignOut']['Session'] = $this->_headers['Session'];
        } else {
            $params['Security_SignOut']['SessionId'] = $this->_headers['SessionId'];
        }

        return $this->soapCall('Security_SignOut', $params);
    }

    /**
     * Command_Cryptic.
     *
     * @param string $string The string to be sent
     *
     * @return Object
     */
    public function commandCryptic($string)
    {
        $params = [];
        $params['Command_Cryptic']['longTextString']['textStringDetails'] = $string;
        $params['Command_Cryptic']['messageAction']['messageFunctionDetails']['messageFunction'] = 'M';

        return $this->soapCall('Command_Cryptic', $params);
    }

    /**
     * Return fare rules.
     *
     * @return Object
     */
    public function checkRules()
    {
        $params = [];
        $params['Fare_CheckRules']['msgType']['messageFunctionDetails']['messageFunction'] = 712;
        $params['Fare_CheckRules']['itemNumber']['itemNumberDetails']['number'] = 1;
        $params['Fare_CheckRules']['fareRule']['tarifFareRule']['ruleSectionId'] = 'PE';

        return $this->soapCall('Fare_CheckRules', $params);
    }

    /**
     * Air_MultiAvailability
     * Check airline availability by Flight.
     *
     * @param string $deprt_date Departure date
     * @param string $deprt_loc Departure location
     * @param string $arrive_loc Arrival location
     * @param string $service Class of service
     * @param string $air_code Airline code
     * @param string $air_num Airline number
     *
     * @return Object
     */
    public function airFlightAvailability($deprt_date, $deprt_loc, $arrive_loc, $service, $air_code, $air_num)
    {
        $params = [];
        $params['Air_MultiAvailability']['messageActionDetails']['functionDetails']['actionCode'] = 44;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['availabilityDetails']['departureDate'] = $deprt_date;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['departureLocationInfo']['cityAirport'] = $deprt_loc;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['arrivalLocationInfo']['cityAirport'] = $arrive_loc;
        $params['Air_MultiAvailability']['requestSection']['optionClass']['productClassDetails']['serviceClass'] = $service;
        $params['Air_MultiAvailability']['requestSection']['airlineOrFlightOption']['flightIdentification']['airlineCode'] = $air_code;
        $params['Air_MultiAvailability']['requestSection']['airlineOrFlightOption']['flightIdentification']['number'] = $air_num;
        $params['Air_MultiAvailability']['requestSection']['availabilityOptions']['productTypeDetails']['typeOfRequest'] = 'TN';

        return $this->soapCall('Air_MultiAvailability', $params);
    }

    /**
     * Air_MultiAvailability
     * Check airline availability by Service.
     *
     * @param string $deprt_date Departure date
     * @param string $deprt_loc Departure location
     * @param string $arrive_loc Arrival location
     * @param string $service Class of service
     * @param string $air_code Airline code
     * @param string $air_num Airline number
     *
     * @return Object
     */
    public function airServiceAvailability($deprt_date, $deprt_loc, $arrive_loc, $service)
    {
        $params = [];
        $params['Air_MultiAvailability']['messageActionDetails']['functionDetails']['actionCode'] = 44;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['availabilityDetails']['departureDate'] = $deprt_date;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['departureLocationInfo']['cityAirport'] = $deprt_loc;
        $params['Air_MultiAvailability']['requestSection']['availabilityProductInfo']['arrivalLocationInfo']['cityAirport'] = $arrive_loc;
        $params['Air_MultiAvailability']['requestSection']['availabilityOptions']['productTypeDetails']['typeOfRequest'] = 'TN';
        $params['Air_MultiAvailability']['requestSection']['cabinOption']['cabinDesignation']['cabinClassOfServiceList'] = $service;

        return $this->soapCall('Air_MultiAvailability', $params);
    }

    /**
     * pnrAddMultiElements
     * Make reservation call.
     *
     * @param PassengerCollection $travellers
     * @param FlightSegmentCollection $segments
     * @param string $validatingCarrier
     * @param string $phoneNumber
     * @param string $email
     * @param AgentCommissions $agentCommission
     *
     * @return Object
     *
     * @throws AmadeusException
     */
    public function pnrAddMultiElements($travellers, $segments, $validatingCarrier, $phoneNumber = null, $email = null, $agentCommission = null)
    {
        $params = [];
        /*
         * 0 - save and close PNR
         * 10 - save and leave PNR open,
         * 11 - different save types with notification on segment status changes
         */
        $params['PNR_AddMultiElements']['pnrActions']['optionCode'] = 0;

        foreach ($travellers->getAdults() as $adult) {
            $adultData = [
                'elementManagementPassenger' => [
                    'reference' => [
                        'qualifier' => 'PR',
                        'number' => $adult->getIndex(),
                    ],
                    'segmentName' => 'NM',
                ],
                'passengerData' => [
                    'travellerInformation' => [
                        'traveller' => [
                            'quantity' => $adult->getAssociatedInfant() == null ? 1 : 2,
                            'surname' => $adult->getLastName(),
                        ],
                        'passenger' => [
                            'firstName' => $adult->getFirstNameWithCode(),
                        ],
                    ],
                ],
            ];

            if ($adult->getAssociatedInfant() != null) {
                $infant = $adult->getAssociatedInfant();
                $adultData['passengerData']['travellerInformation']['passenger']['infantIndicator'] = 3;
                $adultData['passengerData'] = [
                    $adultData['passengerData'],
                    [
                        'travellerInformation' => [
                            'traveller' => [
                                'surname' => $infant->getLastName(),
                            ],
                            'passenger' => [
                                'firstName' => $infant->getFirstNameWithCode(),
                                'type' => 'INF',
                            ],
                        ],
                        'dateOfBirth' => [
                            'dateAndTimeDetails' => [
                                'date' => strtoupper($infant->getBirthday()->format('dMy')),
                            ],
                        ],
                    ],
                ];
            }

            $params['PNR_AddMultiElements']['travellerInfo'][] = $adultData;
        }

        foreach ($travellers->getChildren() as $child) {
            $childData = [
                'elementManagementPassenger' => [
                    'reference' => [
                        'qualifier' => 'PR',
                        'number' => $child->getIndex(),
                    ],
                    'segmentName' => 'NM',
                ],
                'passengerData' => [
                    'travellerInformation' => [
                        'traveller' => [
                            'quantity' => 1,
                            'surname' => $child->getLastName(),
                        ],
                        'passenger' => [
                            'firstName' => $child->getFirstNameWithCode(),
                            'type' => 'CHD',
                        ],
                    ],
                    'dateOfBirth' => [
                        'dateAndTimeDetails' => [
                            'date' => strtoupper($child->getBirthday()->format('dMy')),
                        ],
                    ],
                ],
            ];

            $params['PNR_AddMultiElements']['travellerInfo'][] = $childData;
        }

        //Additional details
        $params['PNR_AddMultiElements']['dataElementsMaster']['marker1'] = null;

        //Unknown
        $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
            'elementManagementData' => [
                'segmentName' => 'RF',
            ],
            'freetextData' => [
                'longFreetext' => 'WS',
            ],
        ];

        //TK
        $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
            'elementManagementData' => [
                'segmentName' => 'TK',
            ],
            'ticketElement' => [
                'passengerType' => 'PAX',
                'ticket' => [
                    'indicator' => 'XL',
                    'date' => $segments->getFirstSegment()->getDepartureDate()->format('dmy'),
                    'time' => str_replace(':', '', $segments->getFirstSegment()->getDepartureTime()),
                ],
            ],
        ];

        //Validating carrier
        $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
            'elementManagementData' => [
                'segmentName' => 'FV',
            ],
            'ticketingCarrier' => [
                'carrier' => [
                    'airlineCode' => $validatingCarrier,
                ],
            ],
        ];

        //Phone number
        if ($phoneNumber != null) {
            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'AP',
                ],
                'freetextData' => [
                    'freetextDetail' => [
                        'subjectQualifier' => 3,
                        'type' => 3,
                    ],
                    'longFreetext' => $phoneNumber,
                ],
            ];
            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'OS',
                ],
                'freetextData' => [
                    'freetextDetail' => [
                        'subjectQualifier' => 3,
                        'type' => 28,
                        'companyId' => 'YY',
                    ],
                    'longFreetext' => 'CTCP' . preg_replace('/\D/', '', $phoneNumber) . '-M',
                ],
            ];
        }

        //Email
        if ($email != null) {
            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'AP',
                ],
                'freetextData' => [
                    'freetextDetail' => [
                        'subjectQualifier' => 3,
                        'type' => 'P02',
                    ],
                    'longFreetext' => $email,
                ],
            ];
        }

        //Form of payment = cash
        $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
            'elementManagementData' => [
                'segmentName' => 'FP',
            ],
            'formOfPayment' => [
                'fop' => [
                    'identification' => 'CA',
                ],
            ],
        ];

        //Internet-booking remark
        $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
            'elementManagementData' => [
                'segmentName' => 'RM',
            ],
            'miscellaneousRemark' => [
                'remarks' => [
                    'type' => 'RM',
                    'freetext' => 'INTERNET BOOKING',
                ],
            ],
        ];

        //Agent commission
        if ($agentCommission != null) {
            $data = [
                'elementManagementData' => [
                    'segmentName' => 'FM',
                ],
                'commission' => [
                    'commissionInfo' => [],
                ],
            ];

            if ($agentCommission->isPercentage()) {
                $data['commission']['commissionInfo']['percentage'] = $agentCommission->getPercent() . '%';
            } else {
                $data['commission']['commissionInfo']['amount'] = $agentCommission->getAmount();
            }

            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = $data;
        }

        foreach (array_merge($travellers->getAdults(), $travellers->getChildren()) as $passenger) {
            /* @var Passenger $passenger */
            $dataElement = [
                'elementManagementData' => [
                    'segmentName' => 'SSR',
                ],
                'serviceRequest' => [
                    'ssr' => [
                        'type' => 'DOCS',
                        'status' => 'HK',
                        'quantity' => 1,
                        'companyId' => 'YY',
                        'freetext' => substr($passenger->ssrDocsText(), 0, 70),
                    ],
                ],
                'referenceForDataElement' => [
                    'reference' => [
                        'qualifier' => 'PR',
                        'number' => $passenger->getIndex(),
                    ],
                ],
            ];

            //Long ssr
            if (strlen($passenger->ssrDocsText()) > 70) {
                $dataElement['serviceRequest']['ssr']['freetext'] = [
                    substr($passenger->ssrDocsText(), 0, 70),
                    substr($passenger->ssrDocsText(), 70, 70),
                ];
            }

            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = $dataElement;

            //FOID
            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'SSR',
                ],
                'serviceRequest' => [
                    'ssr' => [
                        'type' => 'FOID',
                        'status' => 'HK',
                        'quantity' => 1,
                        'companyId' => $validatingCarrier,
                        'freetext' => 'PP' . $passenger->clearedPassport(),
                    ],
                ],
                'referenceForDataElement' => [
                    'reference' => [
                        'qualifier' => 'PR',
                        'number' => $passenger->getIndex(),
                    ],
                ],
            ];

            $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                'elementManagementData' => [
                    'segmentName' => 'FE',
                ],
                'fareElement' => [
                    'generalIndicator' => 'E',
                    'freetextLong' => $validatingCarrier . " ONLY PSPT " . $passenger->clearedPassport(),
                ],
                'referenceForDataElement' => [
                    'reference' => [
                        'qualifier' => 'PR',
                        'number' => $passenger->getIndex(),
                    ],
                ],
            ];

            if ($infant = $passenger->getAssociatedInfant()) {
                $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'SSR',
                    ],
                    'serviceRequest' => [
                        'ssr' => [
                            'type' => 'RM',
                            'status' => 'HK',
                            'quantity' => 1,
                            'companyId' => 'YY',
                            'freetext' => substr($infant->ssrDocsText(), 0, 70),
                        ],
                    ],
                    'referenceForDataElement' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $passenger->getIndex(),
                        ],
                    ],
                ];

                $params['PNR_AddMultiElements']['dataElementsMaster']['dataElementsIndiv'][] = [
                    'elementManagementData' => [
                        'segmentName' => 'FE',
                    ],
                    'fareElement' => [
                        'generalIndicator' => 'E',
                        'passengerType' => 'INF',
                        'freetextLong' => $validatingCarrier . " ONLY PSPT " . $infant->clearedPassport(),
                    ],
                    'referenceForDataElement' => [
                        'reference' => [
                            'qualifier' => 'PR',
                            'number' => $passenger->getIndex(),
                        ],
                    ],
                ];
            }
        }

        return $this->soapCall('PNR_AddMultiElements', $params);
    }

    /**
     * Air_SellFromRecommendation
     * Set travel segments.
     *
     * @param string $from Boarding point
     * @param string $to Destination
     * @param array $segments Travel Segments
     *
     * @return Object
     */
    public function airSellFromRecommendation($from, $to, $segments)
    {
        $params = [];
        $params['Air_SellFromRecommendation']['messageActionDetails']['messageFunctionDetails']['messageFunction'] = 183;
        $params['Air_SellFromRecommendation']['messageActionDetails']['messageFunctionDetails']['additionalMessageFunction'] = 'M1';
        $params['Air_SellFromRecommendation']['itineraryDetails']['originDestinationDetails']['origin'] = $from;
        $params['Air_SellFromRecommendation']['itineraryDetails']['originDestinationDetails']['destination'] = $to;
        $params['Air_SellFromRecommendation']['itineraryDetails']['message']['messageFunctionDetails']['messageFunction'] = 183;

        $i = 0;
        foreach ($segments as $segment) {
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightDate']['departureDate'] = $segment['dep_date'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['boardPointDetails']['trueLocationId'] = $segment['dep_location'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['offpointDetails']['trueLocationId'] = $segment['dest_location'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['companyDetails']['marketingCompany'] = $segment['company'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightIdentification']['flightNumber'] = $segment['flight_no'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['travelProductInformation']['flightIdentification']['bookingClass'] = $segment['class'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['relatedproductInformation']['quantity'] = $segment['passengers'];
            $params['Air_SellFromRecommendation']['itineraryDetails']['segmentInformation'][$i]['relatedproductInformation']['statusCode'] = 'NN';
            $i++;
        }

        return $this->soapCall('Air_SellFromRecommendation', $params);
    }

    /**
     * Fare_PricePNRWithBookingClass.
     *
     * @param string $currency Currency
     *
     * @return Object
     */
    public function farePricePNRWithBookingClass($currency)
    {
        $params = [];

        //Currency override
        $params['Fare_PricePNRWithBookingClass']['pricingOptionGroup'][0] = [
            'pricingOptionKey' => [
                'pricingOptionKey' => 'FCO',
            ],
            'currency' => [
                'firstCurrencyDetails' => [
                    'currencyQualifier' => 'FCO',
                    'currencyIsoCode' => $currency,
                ],
            ],
        ];

        //Published fares
        $params['Fare_PricePNRWithBookingClass']['pricingOptionGroup'][1]['pricingOptionKey']['pricingOptionKey'] = 'RP';

        //Unifares
        $params['Fare_PricePNRWithBookingClass']['pricingOptionGroup'][2]['pricingOptionKey']['pricingOptionKey'] = 'RU';

        return $this->soapCall("Fare_PricePNRWithBookingClass", $params);
    }

    /**
     * @param FlightSegmentCollection $segments
     * @param int $adults
     * @param int $infants
     * @param int $children
     * @param string $currency
     *
     * @return array
     */
    public function fareInformativePricingWithoutPnr($segments, $adults, $infants, $children, $currency)
    {
        $params = [];

        //Adults
        $passengerGroups = [];
        $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
            'quantity' => 1,
            'numberOfUnits' => $adults,
        ];
        for ($i = 1; $i < $adults; $i++) {
            $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i;
        }
        $passengerGroups[] = $passengerGroup;
        $passengerGroup = [];

        //Infants
        if ($infants > 0) {
            $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
                'quantity' => 2,
                'numberOfUnits' => $infants,
            ];
            for ($i = 1; $i <= $infants; $i++) {
                $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i;
            }
            $passengerGroup['discountPtc'] = [
                'valueQualifier' => 'INF',
                'fareDetails' => ['qualifier' => 766],
            ];
            $passengerGroups[] = $passengerGroup;
            $passengerGroup = [];
        }

        //Children
        if ($children > 0) {
            $passengerGroup['segmentRepetitionControl']['segmentControlDetails'] = [
                'quantity' => 3,
                'numberOfUnits' => $children,
            ];
            for ($i = 1; $i <= $children; $i++) {
                $passengerGroup['travellersID'][]['travellerDetails']['measurementValue'] = $i + $adults;
            }
            $passengerGroup['discountPtc'] = [
                'valueQualifier' => 'CH',
            ];
            $passengerGroups[] = $passengerGroup;
        }

        $params['Fare_InformativePricingWithoutPNR']['passengersGroup'] = $passengerGroups;

        //Segments
        $i = 1;
        $segmentsD = [];
        foreach ($segments->getSegments() as $segment) {
            $segmentsD[] = [
                'segmentInformation' => [
                    'flightDate' => [
                        'departureDate' => $segment->getDepartureDate()->format('dmy'),
                        'departureTime' => str_replace(':', '', $segment->getDepartureTime()),
                        'arrivalDate' => $segment->getArrivalDate()->format('dmy'),
                        'arrivalTime' => str_replace(':', '', $segment->getArrivalTime()),
                    ],
                    'boardPointDetails' => [
                        'trueLocationId' => $segment->getDepartureIata(),
                    ],
                    'offpointDetails' => [
                        'trueLocationId' => $segment->getArrivalIata(),
                    ],
                    'companyDetails' => [
                        'marketingCompany' => $segment->getMarketingCarrierIata(),
                        'operatingCompany' => $segment->getOperatingCarrierIata(),
                    ],
                    'flightIdentification' => [
                        'flightNumber' => $segment->getFlightNumber(),
                        'bookingClass' => $segment->getBookingClass(),
                    ],
                    'flightTypeDetails' => [
                        'flightIndicator' => 0,
                    ],
                    'itemNumber' => $i++,
                ],
            ];
        }
        $params['Fare_InformativePricingWithoutPNR']['segmentGroup'] = $segmentsD;

        //Pricing
        //Currency override
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][0] = [
            'pricingOptionKey' => [
                'pricingOptionKey' => 'FCO',
            ],
            'currency' => [
                'firstCurrencyDetails' => [
                    'currencyQualifier' => 'FCO',
                    'currencyIsoCode' => $currency,
                ],
            ],
        ];

        //Published fares
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][1]['pricingOptionKey']['pricingOptionKey'] = 'RP';

        //Unifares
        $params['Fare_InformativePricingWithoutPNR']['pricingOptionGroup'][2]['pricingOptionKey']['pricingOptionKey'] = 'RU';

        return $this->soapCall("Fare_InformativePricingWithoutPNR", $params);
    }

    /**
     * Sends request.
     *
     * @param string $name
     * @param array $params
     *
     * @return string
     *
     * @throws AmadeusException
     * @throws \Exception
     */
    public function soapCall($name, $params)
    {
        $this->_logger->info('Amadeus method called: ' . $name);

        $exc = null;
        try {
            $data = $this->_client->__soapCall($name, $params, null, $this->getHeader(), $this->_headers);
        } catch (\Exception $e) {
            $exc = $e;
        }

        $this->log($params, null);

        if ($exc != null) {
            throw $exc;
        }

        if (isset($data) && isset($data->errorMessage)) {
            throw new AmadeusException($data->errorMessage->applicationError->applicationErrorDetail->error . ' - ' . $data->errorMessage->errorMessageText->description);
        }

        return $this->_client->__getLastResponse();
    }

    /**
     * Ticket_CreateTSTFromPricing.
     *
     * @param integer $types Number of passenger types
     *
     * @return Object
     */
    public function ticketCreateTSTFromPricing($types)
    {
        $params = [];

        for ($i = 0; $i < $types; $i++) {
            $params['Ticket_CreateTSTFromPricing']['psaList'][$i]['itemReference']['referenceType'] = 'TST';
            $params['Ticket_CreateTSTFromPricing']['psaList'][$i]['itemReference']['uniqueReference'] = $i + 1;
        }

        return $this->soapCall('Ticket_CreateTSTFromPricing', $params);
    }

    /**
     * PNR_AddMultiElements
     * Final save operation.
     */
    public function pnrAddMultiElementsFinal()
    {
        $params = [];
        $params['PNR_AddMultiElements']['pnrActions']['optionCode'] = 11;

        return $this->soapCall('PNR_AddMultiElements', $params);
    }

    /**
     * PNR_Retrieve
     * Get PNR by id.
     *
     * @param string $pnr_id PNR ID
     *
     * @return Object
     */
    public function pnrRetrieve($pnr_id)
    {
        $params = [];
        $params['PNR_Retrieve']['retrievalFacts']['retrieve']['type'] = 2;
        $params['PNR_Retrieve']['retrievalFacts']['reservationOrProfileIdentifier']['reservation']['controlNumber'] = $pnr_id;

        return $this->soapCall('PNR_Retrieve', $params);
    }

    /**
     * Recusively dump the variable.
     *
     * @param string $varname Name of the variable
     * @param mixed $varval Vriable to be dumped
     */
    private function dumpVariable($varname, $varval)
    {
        if (!is_array($varval) && !is_object($varval)) {
            print $varname . ' = ' . $varval . "<br>\n";
        } else {
            print $varname . " = data()<br>\n";
            foreach ($varval as $key => $val) {
                $this->dumpVariable($varname . "['" . $key . "']", $val);
            }
        }
    }

    /**
     * Save to log.
     *
     * @param array $params The parameters used
     * @param array $data The response data
     *
     * @return Object
     */
    private function log($params, $data)
    {
        $this->_logger->debug("Request Trace: " . $this->_client->__getLastRequest());
        $this->_logger->debug("Response Trace: " . $this->_client->__getLastResponse());
    }

    /**
     * @return \SoapHeader
     */
    private function getHeader()
    {
        if (isset($this->_headers['Session'])) {
            $soapHeader = new \SoapHeader(InnerClient::AMD_HEAD_NAMESPACE, 'Session', $this->_headers['Session']);
        } else {
            $soapHeader = new \SoapHeader(InnerClient::AMD_HEAD_NAMESPACE, 'SessionId', $this->_headers['SessionId']);
        }

        return $soapHeader;
    }
}
