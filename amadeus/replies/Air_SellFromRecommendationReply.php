<?php

namespace Amadeus\replies;

use Amadeus\models\FlightSegment;
use Amadeus\models\OrderFlow;
use Amadeus\requests\Air_SellFromRecommendationRequest;

class Air_SellFromRecommendationReply extends Reply
{
    /**
     * Check if seats availability confirmed.
     *
     * @return bool
     */
    public function isSuccess()
    {
        $data = $this->xml();
        if (isset($data->errorAtMessageLevel->errorSegment->errorDetails->errorCode) && (string) $data->errorAtMessageLevel->errorSegment->errorDetails->errorCode == '288') {
            return false;
        }

        return true;
    }

    /**
     * Copy data to order flow.
     *
     * @param OrderFlow $orderFlow
     */
    public function copyDataToOrderFlow(OrderFlow &$orderFlow)
    {
        $data = $this->xml();
        $i = 0;

        foreach ($this->iterateStd($data->itineraryDetails->segmentInformation) as $s) {
            $fi = $s->flightDetails;

            /** @var FlightSegment $oldSegmentData */
            $oldSegmentData = $orderFlow->getSegments()->getSegments()[$i];

            $oldSegmentData->setDepartureIata((string)$fi->boardPointDetails->trueLocationId);
            $oldSegmentData->setArrivalIata((string)$fi->offpointDetails->trueLocationId);
            $oldSegmentData->setFlightNumber((string)$fi->flightIdentification->flightNumber);

            $oldSegmentData->setDepartureDate($this->convertAmadeusDate((string)$fi->flightDate->departureDate));
            $oldSegmentData->setDepartureTime($this->convertAmadeusTime((string)$fi->flightDate->departureTime));
            $oldSegmentData->setArrivalDate($this->convertAmadeusDate(isset($fi->flightDate->arrivalDate) ? (string)$fi->flightDate->arrivalDate : (string)$fi->flightDate->departureDate));
            $oldSegmentData->setArrivalTime($this->convertAmadeusTime((string)$fi->flightDate->arrivalTime));

            $oldSegmentData->setTechnicalStopsCount(isset($s->apdSegment->legDetails->numberOfStops) ? (string) $s->apdSegment->legDetails->numberOfStops : 0);
            $oldSegmentData->setEquipmentTypeIata((string) $s->apdSegment->legDetails->equipment);
            if (isset($s->apdSegment->arrivalStationInfo->terminal)) {
                $oldSegmentData->setArrivalTerm((string) $s->apdSegment->arrivalStationInfo->terminal);
            }
            $oldSegmentData->setBookingClass((string) $fi->flightIdentification->bookingClass);

            //Save updated details
            $orderFlow->getSegments()->updateSegment($i++, $oldSegmentData);
        }
    }

    /**
     * @return Air_SellFromRecommendationRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
