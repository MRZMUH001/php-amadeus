<?php

namespace Amadeus\Methods;


use Amadeus\models\PassengerCollection;
use Amadeus\models\TicketPrice;

trait AddMultiPnrTrait
{

    use BasicMethodsTrait;

    /**
     * Add passenger details
     *
     * @param PassengerCollection $passengers
     * @param TicketPrice $ticketPrice
     * @return Object
     */
    public function addMultiPnrTrait($passengers, $ticketPrice)
    {
        return $this->getClient()->pnrAddMultiElements($passengers, $ticketPrice);
    }

}