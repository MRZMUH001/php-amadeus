<?php

namespace Amadeus\models;

class FlightSegmentCollection
{

    /**
     * Flight segments
     * @var FlightSegment[]
     */
    private $_segements = [];

    /**
     * Estimated flight price
     * @var int
     */
    private $_estimatedFlightTime;

    /**
     * @return FlightSegment[]
     */
    public function getSegements()
    {
        return $this->_segements;
    }

    /**
     * Add one more segment
     * @param FlightSegment $segment
     */
    public function addSegment(FlightSegment $segment)
    {
        $this->_segements[] = $segment;
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