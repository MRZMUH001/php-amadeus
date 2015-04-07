<?php

namespace amadeus\methods;

use amadeus\models\OrderFlow;

trait PnrAddMultiElementsTrait
{
    use BasicMethodsTrait;

    /**
     * Add passenger details.
     *
     * @param OrderFlow $orderFlow
     *
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

        return;
    }
}
