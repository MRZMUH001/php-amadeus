<?php

namespace Amadeus\Methods;

use Amadeus\InnerClient;

trait BasicMethodsTrait
{
    /**
     * @return InnerClient
     */
    protected abstract function getClient();

    /**
     * Used to iterate std classed
     * Will return multiple subobjects for iterable std and one if non-iterable
     * @param $std
     * @return \Generator
     */
    protected abstract function iterateStd($std);

    /**
     * Convert amadeus date format (010515) to DateTime
     * @param string $date
     * @return \DateTime date
     */
    protected abstract function convertAmadeusDate($date);

    /**
     * Convert amadeus time format (2240) to 22:40
     * @param string $time
     * @return string
     */
    protected abstract function convertAmadeusTime($time);

    /**
     * Convert from 0130 to 90 minutes
     * @param string $duration
     * @return int minutes
     */
    protected abstract function convertAmadeusDurationToMinutes($duration);
}