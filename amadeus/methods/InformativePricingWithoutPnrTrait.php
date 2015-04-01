<?php

namespace Amadeus\Methods;


use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketPrice;

trait InformativePricingWithoutPnrTrait
{

    use BasicMethodsTrait;

    /**
     * @param TicketPrice $ticketPrice
     * @param SimpleSearchRequest $request
     * @return array
     */
    public function informativePricingWithoutPnr($ticketPrice, $request)
    {
        $data = $this->getClient()->fareInformativePricingWithoutPnr($ticketPrice, $request->getAdults(), $request->getInfants(), $request->getChildren(), $request->getCurrency());

        return $data;
    }

}