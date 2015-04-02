<?php

namespace Amadeus\Methods;


use Amadeus\models\PassengerCollection;
use Amadeus\models\TicketDetails;

trait AddMultiPnrTrait
{

    use BasicMethodsTrait;

    /**
     * Add passenger details
     *
     * @param PassengerCollection $passengers
     * @param TicketDetails $ticketDetails
     * @param string $validatingCarrier
     * @param string $email
     * @param string $phone
     * @return Object
     */
    public function pnrAddMultiElements($passengers, $ticketDetails, $validatingCarrier, $email, $phone)
    {
        $data = $this->getClient()->pnrAddMultiElements($passengers, $ticketDetails, $phone, $email);
        if (isset($data->reservationInfo->reservation->controlNumber))
            return (string)$data->reservationInfo->reservation->controlNumber;

        return null;
    }

}