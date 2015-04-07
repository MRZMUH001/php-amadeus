<?php

namespace Amadeus\replies;


use Amadeus\models\OrderFlow;
use Amadeus\requests\PNR_AddMultiElementsRequest;

class PNR_AddMultiElementsReply extends Reply
{

    public function copyDataToOrderFlow(OrderFlow &$orderFlow)
    {
        if ($pnr = $this->pnrNumber())
            $orderFlow->setPnr($pnr);
    }

    /**
     * Get errors if they exists
     *
     * @return string[]|null
     */
    public function getErrors()
    {
        //TODO:
        return null;
    }

    /**
     * PNR Number
     *
     * @return null|string
     */
    public function pnrNumber()
    {
        $data = $this->xml();
        if (isset($data->pnrHeader->reservationInfo->reservation->controlNumber)) {
            return (string)$data->pnrHeader->reservationInfo->reservation->controlNumber;
        }

        return null;
    }

    /**
     * @return PNR_AddMultiElementsRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}