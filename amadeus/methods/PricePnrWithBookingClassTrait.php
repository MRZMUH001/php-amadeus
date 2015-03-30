<?php

namespace Amadeus\Methods;

trait PricePnrWithBookingClassTrait
{

    use BasicMethodsTrait;

    public function pricePnrWithBookingClass($currency)
    {
        return $this->_ws->farePricePNRWithBookingClass($currency);
    }

}