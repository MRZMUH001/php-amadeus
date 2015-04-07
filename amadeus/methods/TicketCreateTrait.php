<?php

namespace amadeus\methods;

trait TicketCreateTrait
{
    use BasicMethodsTrait;

    public function ticketCreate()
    {
        $this->getClient()->ticketCreateTSTFromPricing();
    }
}
