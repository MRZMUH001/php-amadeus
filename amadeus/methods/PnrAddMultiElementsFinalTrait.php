<?php

namespace amadeus\methods;

trait PnrAddMultiElementsFinalTrait
{
    use BasicMethodsTrait;

    public function pnrAddMultiElementsFinal()
    {
        $data = $this->getClient()->pnrAddMultiElementsFinal();
        if (isset($data->pnrHeader->reservationInfo->reservation->controlNumber)) {
            return (string) $data->pnrHeader->reservationInfo->reservation->controlNumber;
        }

        return;
    }
}
