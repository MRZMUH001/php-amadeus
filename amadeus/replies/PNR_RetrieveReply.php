<?php

namespace Amadeus\replies;

use Amadeus\requests\PNR_RetrieveRequest;

class PNR_RetrieveReply extends Reply
{

    /**
     * PNR Number
     *
     * @return string
     */
    public function getPnrNumber()
    {
        return (string)$this->xml()->xpath('//reservationInfo/reservation/controlNumber')[0];
    }

    /**
     * Client e-mail
     *
     * @return string
     */
    public function getClientEmail()
    {
        return (string)$this->xml()->xpath('//otherDataFreetext[freetextDetail/type="P02"]/longFreetext')[0];
    }

    /**
     * Client phone
     *
     * @return string
     */
    public function getClientPhone()
    {
        return (string)$this->xml()->xpath('//otherDataFreetext[freetextDetail/type=3]/longFreetext')[0];
    }

    /**
     * @return PNR_RetrieveRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}