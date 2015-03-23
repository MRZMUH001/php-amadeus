<?php

namespace Amadeus\Methods;

use Amadeus\InnerClient;

trait BasicMethodsTrait
{

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


    function __construct()
    {
        // Instantiate the Amadeus class (Debug enabled)
        $this->_ws = new InnerClient(realpath(dirname(__FILE__)) . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR . 'AmadeusWebServices.wsdl', true);
    }

    /**
     * Open new amadeus session
     * @param string $officeId
     * @param string $originator
     * @param string $organization
     * @param string $password
     */
    public function openSession($officeId, $originator, $organization, $password)
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
    private function iterateStd($std)
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
    public function closeSession()
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
        $dt = \DateTime::createFromFormat('Hm', $time);
        return $dt->format('H:m');
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

}