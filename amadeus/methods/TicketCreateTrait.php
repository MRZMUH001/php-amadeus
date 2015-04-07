<?php

namespace Amadeus\methods;

trait TicketCreateTrait
{
    use BasicMethodsTrait;

    public function ticketCreate()
    {
        $this->getClient()->ticketCreateTSTFromPricing();
    }
}
