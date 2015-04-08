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
     * Response headers.
     */
    private $_headers = null;

    /**
     * Hold the client object.
     *
     * @var \SoapClient
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

        $this->_logger->debug("Request Trace: " . $this->_client->__getLastRequest());
        $this->_logger->debug("Response Trace: " . $this->_client->__getLastResponse());

        if ($exc != null) {
            throw $exc;
        }

        if (isset($data) && isset($data->errorMessage)) {
            throw new AmadeusException($data->errorMessage->applicationError->applicationErrorDetail->error . ' - ' . $data->errorMessage->errorMessageText->description);
        }

        return $this->_client->__getLastResponse();
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
