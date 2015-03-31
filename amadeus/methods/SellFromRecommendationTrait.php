<?php

namespace Amadeus\Methods;


use amadeus\exceptions\UnableToSellException;
use Amadeus\models\FlightSegment;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\TicketDetails;
use Amadeus\models\TicketPrice;

trait SellFromRecommendationTrait
{

    use BasicMethodsTrait;

    /**
     *
     * @param TicketPrice $ticketPrice
     * @param int $passengers Number of passengers
     *
     * @throws UnableToSellException
     * @return TicketDetails
     */
    public function sellFromRecommendation(TicketPrice $ticketPrice, $passengers)
    {
        $segments = [];

        $i = 0;
        foreach ($ticketPrice->getSegments()->getSegements() as $segment) {
            $segments[] = [
                'dep_date' => $segment->getDepartureDate()->format('dmy'),
                'dep_location' => $segment->getDepartureIata(),
                'dest_location' => $segment->getArrivalIata(),
                'company' => $segment->getMarketingCarrierIata(),
                'flight_no' => $segment->getFlightNumber(),
                'class' => $ticketPrice->getBookingClasses()[$i],
                'passengers' => $passengers
            ];
            $i++;
        }

        $data = $this->getClient()->airSellFromRecommendation(
            $ticketPrice->getDepartureIata(),
            $ticketPrice->getArrivalIata(),
            $segments
        );

        if (isset($data->errorAtMessageLevel->errorSegment->errorDetails->errorCode) && $data->errorAtMessageLevel->errorSegment->errorDetails->errorCode == '288')
            throw new UnableToSellException("Not all segments are confirmed");

        $ticketDetails = new TicketDetails();
        foreach ($this->iterateStd($data->itineraryDetails->segmentInformation) as $s) {
            $fi = $s->flightDetails;
            $segment = new FlightSegment(
                (string)$fi->companyDetails->marketingCompany,//TODO: Not really right, need to find operating carrier
                (string)$fi->companyDetails->marketingCompany,
                (string)$fi->boardPointDetails->trueLocationId,
                (string)$fi->offpointDetails->trueLocationId,
                (string)$fi->flightIdentification->flightNumber,
                $this->convertAmadeusDate(isset($fi->flightDate->arrivalDate) ? (string)$fi->flightDate->arrivalDate : (string)$fi->flightDate->departureDate),
                $this->convertAmadeusTime((string)$fi->flightDate->arrivalTime),
                $this->convertAmadeusDate((string)$fi->flightDate->departureDate),
                $this->convertAmadeusTime((string)$fi->flightDate->departureTime)
            );
            $segment->setEquipmentTypeIata((string)$s->apdSegment->legDetails->equipment);
            $segment->setArrivalTerm((string)$s->apdSegment->arrivalStationInfo->terminal);
            $ticketDetails->addSegment($segment);
        }

        return $ticketDetails;
    }

}