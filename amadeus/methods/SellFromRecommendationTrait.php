<?php

namespace Amadeus\Methods;


use Amadeus\models\TicketPrice;

trait SellFromRecommendationTrait
{

    use BasicMethodsTrait;

    /**
     *
     * @param TicketPrice $ticketPrice
     * @param int $passengers Number of passengers
     */
    public function sellFromRecommendation(TicketPrice $ticketPrice,$passengers)
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

        print_r($data);
    }

}