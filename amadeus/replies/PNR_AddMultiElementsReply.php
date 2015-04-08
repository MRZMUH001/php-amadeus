<?php

namespace Amadeus\replies;


use Amadeus\models\OrderFlow;
use Amadeus\requests\PNR_AddMultiElementsRequest;

class PNR_AddMultiElementsReply extends PNR_RetrieveReply
{

    public function copyDataToOrderFlow(OrderFlow &$orderFlow)
    {
        if ($pnr = $this->getPnrNumber())
            $orderFlow->setPnr($pnr); else
            throw new \Exception("PNR not received");
    }

    /**
     * Return passenger numbers
     *
     * @return array number=>[first=>'',last=>'']
     */
    public function getPassengerNumbers()
    {
        $data = $this->xml();

        $travellers = [];
        foreach ($this->iterateStd($data->travellerInfo) as $traveller) {
            //PT-number
            $number = (string)$traveller->elementManagementPassenger->reference->number;

            $firstNameWithCode = (string)$traveller->passengerData->travellerInformation->passenger->firstName;
            $lastName = (string)$traveller->passengerData->travellerInformation->traveller->surname;

            $travellers[$number] = [
                'first' => $firstNameWithCode,
                'last' => $lastName
            ];
        }

        return $travellers;
    }

    /**
     * Check if request was succesfull
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return
            $this->xml()->xpath('//generalErrorInfo/messageErrorInformation/errorDetail[qualifier="EC"]') === null &&
            $this->xml()->xpath('//nameError/nameErrorInformation/errorDetail[qualifier="EC"]') === null &&
            $this->xml()->xpath('//dataElementsIndiv[elementManagementData/status="ERR"]') === null;
    }

    /**
     * Name errors
     *
     * @return string[]
     */
    public function getNameErrors()
    {
        $errors = $this->xml()->xpath('//travellerInfo/nameError/nameErrorFreeText/text');

        $errors = array_map(function ($xml) {
            return (string)$xml;
        }, $errors);
        return $errors;
    }

    /**
     * SRFoid errors
     *
     * @return string[]
     */
    public function getSRFoidErrors()
    {
        $errors = $this->xml()->xpath('//dataElementsIndiv[elementManagementData/status="ERR"][serviceRequest/ssr/type="FOID"]/elementErrorInformation/elementErrorText/text');

        $errors = array_map(function ($xml) {
            return (string)$xml;
        }, $errors);
        return $errors;
    }

    /**
     * Get errors if they exists
     *
     * @return string[]|null
     */
    public function getErrors()
    {
        $errors = [
            $this->xml()->xpath('//messageErrorText/text'),
            $this->xml()->xpath('//elementErrorInformation/elementErrorText/text'),
            $this->getNameErrors(),
            $this->getSRFoidErrors()
        ];

        $stringErrors = [];
        foreach ($errors as $errorList) {
            if ($errorList != null && $errorList != 'null') {
                foreach ($errorList as $err)
                    $stringErrors[] = trim((string)$err);
            }
        }

        return $stringErrors;
    }

    /**
     * @return PNR_AddMultiElementsRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}