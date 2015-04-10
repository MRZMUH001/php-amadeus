<?php

namespace Amadeus\replies;

use Amadeus\requests\PNR_AddMultiElementsRequest;

class PNR_AddMultiElementsReply extends PNR_RetrieveReply
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
     * Additional PNR Numbers
     *
     * @return array marketingCarrier => PNR Number
     */
    public function getAdditionalPnrNumbers()
    {
        $results = [];
        foreach ($this->xml()->xpath('//itineraryReservationInfo/reservation') as $ri) {
            $marketingCarrier = (string)$ri->xpath('companyId')[0];
            $pnrNumber = (string)$ri->xpath('controlNumber')[0];
            $results[$marketingCarrier] = $pnrNumber;
        }
        return $results;
    }

    /**
     * Get all booking classes
     *
     * @return array
     */
    public function booking_classes()
    {
        $results = [];
        foreach (
            $this->xml()->xpath("//itineraryInfo[elementManagementItinerary/segmentName='AIR']" .
                "[travelProduct/companyDetail/identification]" .
                "/travelProduct/productDetails/classOfService") as $c
        )
            $results[] = (string)$c;

        return $results;
    }

    /**
     * Get all cabins
     *
     * @return array
     */
    public function cabins()
    {
        $results = [];
        foreach (
            $this->xml()->xpath("//itineraryInfo[elementManagementItinerary/segmentName='AIR']" .
                "[travelProduct/companyDetail/identification]" .
                "/cabinDetails/cabinDetails/classDesignator") as $c
        )
            $results[] = (string)$c;

        return $results;
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
     * @return PNR_AddMultiElementsRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}