<?php

namespace Amadeus\Methods;


use Amadeus\models\PassengerCollection;
use Amadeus\models\TicketDetails;

trait PnrAddMultiElementsTrait
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
     * @param AgentCommission $agentCommission
     * @return Object
     */
    public function pnrAddMultiElements($passengers, $ticketDetails, $validatingCarrier, $email, $phone, $agentCommission = null)
    {
        $data = $this->getClient()->pnrAddMultiElements($passengers, $ticketDetails, $validatingCarrier, $phone, $email, $agentCommission);

        return null;
    }

}