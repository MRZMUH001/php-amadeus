<?php

namespace Amadeus\replies;

use Amadeus\requests\Ticket_CreateTSTFromPricingRequest;

class Ticket_CreateTSTFromPricingReply extends Reply
{

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->xml()->xpath('//tstList') != null;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return (string)$this->xml()->xpath('//errorText/errorFreeText');
    }

    /**
     * @return Ticket_CreateTSTFromPricingRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}