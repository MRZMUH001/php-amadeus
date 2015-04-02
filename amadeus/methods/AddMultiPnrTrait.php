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
     * @param string $email
     * @param string $phone
     * @return Object
     */
    public function addMultiPnrTrait($passengers, $ticketPrice, $email, $phone)
    {
        return $this->getClient()->pnrAddMultiElements($passengers, $ticketPrice, $phone, $email);
    }

}