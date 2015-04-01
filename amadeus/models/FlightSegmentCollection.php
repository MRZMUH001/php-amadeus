<?php

namespace Amadeus\models;

class FlightSegmentCollection
{

    /**
     * Flight segments
     * @var FlightSegment[]
     */
    private $_segments = [];

    /**
     * Estimated flight price
     * @var int
     */
    private $_estimatedFlightTime;

    /**
     * @return FlightSegment[]
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * Add one more segment
     * @param FlightSegment $segment
     */
    public function addSegment(FlightSegment $segment)
    {
        $this->_segments[] = $segment;
    }

    /**
     * Flight time in minutes
     * @return int
     */
    public function getEstimatedFlightTime()
    {
        return $this->_estimatedFlightTime;
    }

    /**
     * @param int $estimatedFlightTime Flight time in minutes
     */
    public function setEstimatedFlightTime($estimatedFlightTime)
    {
        $this->_estimatedFlightTime = $estimatedFlightTime;
    }

}