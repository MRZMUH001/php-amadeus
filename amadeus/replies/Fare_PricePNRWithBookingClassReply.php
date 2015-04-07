<?php

namespace Amadeus\replies;

use Amadeus\models\BagAllowance;
use Amadeus\models\Fare;
use Amadeus\models\OrderFlow;
use Amadeus\models\Price;
use Amadeus\requests\Fare_PricePNRWithBookingClassRequest;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

class Fare_PricePNRWithBookingClassReply extends Reply
{
    public function getFares()
    {
        $data = $this->xml();
        $fares = [];

        foreach ($this->iterateStd($data->fareList) as $fare) {
            $peopleCount = count($fare->paxSegReference->refDetails);

            $class = (string)$fare->segmentInformation->segDetails->segmentDetail->classOfService;
            $newFare = new Fare();
            $newFare->setValidatingCarrier((string)$fare->validatingCarrier->carrierInformation->carrierCode);
            $newFare->setClass($class);
            $newFare->setPeopleCount($peopleCount);

            //Iterate via fare parts
            foreach ($this->iterateStd($fare->fareDataInformation->fareDataSupInformation) as $f) {
                if (isset($f->fareAmount)) {
                    $newFare->addFareType(
                        (string)$f->fareDataQualifier,
                        Money::fromString((string)$f->fareAmount, new Currency((string)$f->fareCurrency))
                    );
                }
            }

            $bagAllowanceInformation = $fare->segmentInformation->bagAllowanceInformation->bagAllowanceDetails;
            $bagAllowance = new BagAllowance(
                isset($bagAllowanceInformation->baggageWeight) ? (string)$bagAllowanceInformation->baggageWeight : null,
                (string)$bagAllowanceInformation->baggageType,
                isset($bagAllowanceInformation->measureUnit) ? (string)$bagAllowanceInformation->measureUnit : null,
                isset($bagAllowanceInformation->baggageQuantity) ? (string)$bagAllowanceInformation->baggageQuantity : null
            );

            $newFare->setBagAllowance($bagAllowance);

            $fares[$class] = $newFare;
        }

        return $fares;
    }

    public function copyDataToOrderFlow(OrderFlow &$orderFlow)
    {
        $data = $this->xml();

        $fareList = [];
        foreach ($this->iterateStd($data->fareList->fareDataInformation->fareDataSupInformation) as $f) {
            if (isset($f->fareAmount)) {
                $fareList[(string)$f->fareDataQualifier] = Money::fromString(
                    (string)$f->fareAmount,
                    new Currency((string)$f->fareCurrency)
                );
            }
        }

        $taxesList = [];
        foreach ($this->iterateStd($data->fareList->taxInformation) as $t) {
            $taxesList[] = Money::fromString(
                (string)$t->amountDetails->fareDataMainInformation->fareAmount,
                new Currency((string)$t->amountDetails->fareDataMainInformation->fareCurrency)
            );
        }

        $lastTktDate = \DateTime::createFromFormat('d-m-Y',
            implode('-', [
                $data->fareList->lastTktDate->dateTime->day,
                $data->fareList->lastTktDate->dateTime->month,
                $data->fareList->lastTktDate->dateTime->year,
            ])
        );

        $i = 0;

        foreach ($this->iterateStd($data->fareList->segmentInformation) as $s) {
            $oldSegment = $orderFlow->getSegments()->getSegment($i);

            $classOfService = $s->segDetails->segmentDetail->classOfService;
            $bagAllowanceInformation = $s->bagAllowanceInformation->bagAllowanceDetails;
            $bagAllowance = new BagAllowance(
                isset($bagAllowanceInformation->baggageWeight) ? (string)$bagAllowanceInformation->baggageWeight : null,
                (string)$bagAllowanceInformation->baggageType,
                isset($bagAllowanceInformation->measureUnit) ? (string)$bagAllowanceInformation->measureUnit : null,
                isset($bagAllowanceInformation->baggageQuantity) ? (string)$bagAllowanceInformation->baggageQuantity : null
            );
            $oldSegment->setBookingClass($classOfService);
            $oldSegment->setBagAllowance($bagAllowance);
            $orderFlow->getSegments()->updateSegment($i, $oldSegment);

            $i++;
        }

        /** @var Money $totalFare */
        $totalFare = $fareList[712];

        /** @var Money $baseFare */
        $baseFare = $fareList['B'];

        /** @var Money $tax */
        $tax = $totalFare->subtract($baseFare);

        $price = new Price($baseFare, $tax);

        //Set commission
        $commissions = $this->getClient()->getCommissions($orderFlow->getSegments(), $orderFlow->getValidatingCarrier(), $orderFlow->getSearchRequest());
        if ($commissions == null) {
            throw new \Exception("No commissions found");
        }

        $commissions->apply($price, $orderFlow->getSearchRequest());

        $orderFlow->setPrice($price);
        $orderFlow->setCommissions($commissions);

        $orderFlow->setLastTktDate($lastTktDate);
    }

    /**
     * @return Fare_PricePNRWithBookingClassRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
