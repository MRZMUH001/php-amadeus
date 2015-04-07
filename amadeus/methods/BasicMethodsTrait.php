<?php

namespace amadeus\methods;

use Amadeus\InnerClient;
use Amadeus\models\AgentCommissions;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\SimpleSearchRequest;

trait BasicMethodsTrait
{
    /**
     * @return InnerClient
     */
    abstract protected function getClient();

    /**
     * Used to iterate std classed
     * Will return multiple subobjects for iterable std and one if non-iterable.
     *
     * @param $std
     *
     * @return \Generator
     */
    abstract protected function iterateStd($std);

    /**
     * Convert amadeus date format (010515) to DateTime.
     *
     * @param string $date
     *
     * @return \DateTime date
     */
    abstract protected function convertAmadeusDate($date);

    /**
     * Convert amadeus time format (2240) to 22:40.
     *
     * @param string $time
     *
     * @return string
     */
    abstract protected function convertAmadeusTime($time);

    /**
     * Convert from 0130 to 90 minutes.
     *
     * @param string $duration
     *
     * @return int minutes
     */
    abstract protected function convertAmadeusDurationToMinutes($duration);

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
}
