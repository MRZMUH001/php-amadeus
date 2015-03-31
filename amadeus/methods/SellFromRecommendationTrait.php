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
        $newSegments = new FlightSegmentCollection();
        foreach ($this->iterateStd($data->itineraryDetails->segmentInformation) as $s) {
            $fi = $s->flightDetails;
            $segment = new FlightSegment(
                "",
                (string)$fi->companyDetails->marketingCompany,
                (string)$fi->boardPointDetails->trueLocationId,
                (string)$fi->offpointDetails->trueLocationId,
                (string)$fi->flightIdentification->flightNumber,
                $this->convertAmadeusDate((string)$fi->flightDate->departureDate),
                $this->convertAmadeusTime((string)$fi->flightDate->departureTime),
                $this->convertAmadeusDate(isset($fi->flightDate->arrivalDate) ? (string)$fi->flightDate->departureDate : (string)$fi->flightDate->arrivalDate),
                $this->convertAmadeusTime((string)$fi->flightDate->arrivalTime)
            );
            $newSegments->addSegment($segment);
        }

        $ticketDetails->setSegmentDetails($newSegments);

        return $ticketDetails;
    }

}