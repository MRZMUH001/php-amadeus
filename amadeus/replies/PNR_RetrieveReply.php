<?php

namespace Amadeus\replies;


use Amadeus\requests\PNR_RetrieveRequest;

class PNR_RetrieveReply extends Reply
{



    /**
     * @return PNR_RetrieveRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}