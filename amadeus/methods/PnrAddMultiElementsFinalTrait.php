<?php

namespace Amadeus\Methods;


trait PnrAddMultiElementsFinalTrait
{

    use BasicMethodsTrait;

    public function pnrAddMultiElementsFinal()
    {
        $data = $this->getClient()->pnrAddMultiElementsFinal();
        if (isset($data->pnrHeader->reservationInfo->reservation->controlNumber))
            return (string)$data->pnrHeader->reservationInfo->reservation->controlNumber;

        return null;
    }

}