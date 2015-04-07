<?php

namespace Amadeus\replies;


use Amadeus\requests\PNR_AddMultiElementsRequest;

class PNR_AddMultiElementsReply extends Reply {

    /**
     * @return PNR_AddMultiElementsRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}