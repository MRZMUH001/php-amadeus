<?php

namespace amadeus\replies;

use amadeus\models\FlightSegment;
use amadeus\models\OrderFlow;
use amadeus\requests\Air_SellFromRecommendationRequest;

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

            /*$segment = new FlightSegment(
                (string)$fi->companyDetails->marketingCompany,//TODO: Not really right, need to find operating carrier
                (string)$fi->companyDetails->marketingCompany,
                (string)$fi->boardPointDetails->trueLocationId,
                (string)$fi->offpointDetails->trueLocationId,
                (string)$fi->flightIdentification->flightNumber,
                $this->convertAmadeusDate(isset($fi->flightDate->arrivalDate) ? (string)$fi->flightDate->arrivalDate : (string)$fi->flightDate->departureDate),
                $this->convertAmadeusTime((string)$fi->flightDate->arrivalTime),
                $this->convertAmadeusDate((string)$fi->flightDate->departureDate),
                $this->convertAmadeusTime((string)$fi->flightDate->departureTime)
            );*/

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
