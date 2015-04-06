<?php

namespace Amadeus\Methods;


trait TicketCreateTrait
{

    use BasicMethodsTrait;

    public function ticketCreate()
    {
        $this->getClient()->ticketCreateTSTFromPricing();
    }

}