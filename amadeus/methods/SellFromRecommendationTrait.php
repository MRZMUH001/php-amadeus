<?php

namespace Amadeus\methods;

use Amadeus\exceptions\UnableToSellException;
use Amadeus\models\FlightSegment;
use Amadeus\models\OrderFlow;

trait SellFromRecommendationTrait
{
    use BasicMethodsTrait;

    /**
     * Checks if all segments are confirmed and return segments data.
     *
     * @param OrderFlow $orderFlow
     *
     * @return OrderFlow
     *
     * @throws UnableToSellException
     */
    public function sellFromRecommendation(OrderFlow $orderFlow)
    {
        $segments = [];

        $i = 0;
        foreach ($orderFlow->getSegments()->getSegments() as $segment) {
            $segments[] = [
                'dep_date' => $segment->getDepartureDate()->format('dmy'),
                'dep_location' => $segment->getDepartureIata(),
                'dest_location' => $segment->getArrivalIata(),
                'company' => $segment->getMarketingCarrierIata(),
                'flight_no' => $segment->getFlightNumber(),
                'class' => $segment->getBookingClass(),
                'passengers' => $orderFlow->getSearchRequest()->getSeats(),
            ];
            $i++;
        }

        $data = $this->getClient()->airSellFromRecommendation(
            $orderFlow->getSegments()->getFirstSegment()->getDepartureIata(),
            $orderFlow->getSegments()->getLastSegment()->getArrivalIata(),
            $segments
        );

        if (isset($data->errorAtMessageLevel->errorSegment->errorDetails->errorCode) && $data->errorAtMessageLevel->errorSegment->errorDetails->errorCode == '288') {
            throw new UnableToSellException("Not all segments are confirmed");
        }

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

        return $orderFlow;
    }
}
