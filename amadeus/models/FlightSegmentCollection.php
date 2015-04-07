<?php

namespace amadeus\models;

class FlightSegmentCollection
{
    /**
     * Flight segments.
     *
     * @var FlightSegment[]
     */
    private $_segments = [];

    /**
     * Estimated flight price.
     *
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
     * Add one more segment.
     *
     * @param FlightSegment $segment
     */
    public function addSegment(FlightSegment $segment)
    {
        $this->_segments[] = $segment;
    }

    /**
     * Flight time in minutes.
     *
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

    /**
     * @return FlightSegment
     */
    public function getFirstSegment()
    {
        return $this->_segments[0];
    }

    /**
     * @return FlightSegment
     */
    public function getLastSegment()
    {
        return $this->_segments[count($this->_segments) - 1];
    }

    /**
     * @param int $i index
     *
     * @return FlightSegment
     */
    public function getSegment($i)
    {
        return $this->_segments[$i];
    }

    /**
     * @param int           $i
     * @param FlightSegment $segment
     */
    public function updateSegment($i, $segment)
    {
        $this->_segments[$i] = $segment;
    }
}
