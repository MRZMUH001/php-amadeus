<?php

namespace Amadeus\replies;

use Amadeus\Client;
use Amadeus\requests\Request;

abstract class Reply
{
    /** @var \SimpleXMLElement */
    private $_xml;

    /** @var  Request */
    protected $_request;

    /** @var  Client */
    private $_client;

    /**
     * Response constructor.
     *
     * @param Client $client
     * @param Request $request
     * @param string $answer
     */
    public function __construct(Client $client, $request, $answer)
    {
        $this->_client = $client;
        $this->_request = $request;

        $answer = str_replace('soap:', '', $answer);
        $answer = preg_replace('/ xmlns=\"[^\"]+\"/', '', $answer);

        //Get class name
        $classParts = explode('\\', get_called_class());
        $className = end($classParts);

        $xml = simplexml_load_string($answer);
        $this->_xml = $xml->Body->children()[0];
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @return \SimpleXMLElement|\SimpleXMLElement[]
     */
    public function xml()
    {
        return $this->_xml;
    }

    /**
     * @return Request
     */
    abstract public function getRequest();

    //Helpers ----------------------------------------------------------------------------------------------------------


    /**
     * Convert Amadeus date format (010515) to DateTime.
     *
     * @param string $date
     *
     * @return \DateTime date
     */
    protected function convertAmadeusDate($date)
    {
        return \DateTime::createFromFormat('dmy', $date);
    }

    /**
     * Convert Amadeus time format (2240) to 22:40.
     *
     * @param string $time
     *
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
     * Convert from 0130 to 90 minutes.
     *
     * @param string $duration
     *
     * @return int minutes
     */
    protected function convertAmadeusDurationToMinutes($duration)
    {
        return intval($duration / 100) * 60 + $duration % 100;
    }

    /**
     * Used to iterate std classed
     * Will return multiple subobjects for iterable std and one if non-iterable.
     *
     * @param $std
     *
     * @return \Generator
     */
    protected function iterateStd(&$std)
    {
        if (isset($std[0])) {
            $i = 0;
            while (isset($std[$i]))
                yield $std[$i++];
        } else {
            yield $std;
        }
    }
}
