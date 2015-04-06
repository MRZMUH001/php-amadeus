<?php

namespace Amadeus\Methods;


use Amadeus\models\OrderFlow;
use Amadeus\models\PassengerCollection;
use Amadeus\models\TicketDetails;

trait PnrAddMultiElementsTrait
{

    use BasicMethodsTrait;

    /**
     * Add passenger details
     *
     * @param OrderFlow $orderFlow
     * @return Object
     */
    public function pnrAddMultiElements(OrderFlow $orderFlow)
    {
        $data = $this->getClient()->pnrAddMultiElements(
            $orderFlow->getPassengers(),
            $orderFlow->getSegments(),
            $orderFlow->getValidatingCarrier(),
            $orderFlow->getClientPhone(),
            $orderFlow->getClientEmail(),
            $orderFlow->getCommissions()
        );

        return null;
    }

}