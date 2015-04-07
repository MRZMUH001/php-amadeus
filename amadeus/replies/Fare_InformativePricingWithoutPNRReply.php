<?php

namespace Amadeus\replies;

use Amadeus\models\FaresPerPassengers;
use Amadeus\models\OrderFlow;
use Amadeus\models\Price;
use Amadeus\requests\Fare_InformativePricingWithoutPNRRequest;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

class Fare_InformativePricingWithoutPNRReply extends Reply
{
    public function copyDataToOrderFlow(OrderFlow &$orderFlow)
    {
        $data = $this->xml();

        $totalFares = [];
        $totalTaxes = [];
        $segmentDetails = [];
        $isPublishedFare = $data->mainGroup->generalIndicatorsGroup->generalIndicators->priceTicketDetails->indicators == 'I';

        $faresPerPassengers = new FaresPerPassengers();

        //Iterate via passenger groups
        $groupNumber = 0;
        foreach ($this->iterateStd($data->mainGroup->pricingGroupLevelGroup) as $g) {

            $personsType = (string)$g->fareInfoGroup->segmentLevelGroup[0]->ptcSegment->quantityDetails->unitQualifier;
            $personsCount = (string)$g->numberOfPax->segmentControlDetails->numberOfUnits;

            //Fares
            /** @var Money[] $totalFares */
            $fares = [];
            foreach ($g->fareInfoGroup->fareAmount->children() as $d) {
                $fare = Money::fromString((string)(floatval((string)$d->amount) * $personsCount), new Currency((string)$d->currency));
                $typeQualifier = (string)$d->typeQualifier;
                $fares[$typeQualifier] = $fare;
                $totalFares[$typeQualifier] = isset($totalFares[$typeQualifier]) ? $fare->add($totalFares[$typeQualifier]) : $fare;
            }

            switch ($personsType) {
                case 'ADT':
                    $faresPerPassengers->setAdultsFare($fares['B']);
                    break;
                case 'IN':
                    $faresPerPassengers->setInfantsFare($fares['B']);
                    break;
                case 'CH':
                    $faresPerPassengers->setChildrenFare($fares['B']);
                    break;
            }

            //Taxes
            /** @var Money[] $totalTaxes */
            if (isset($g->fareInfoGroup->surchargesGroup->taxesAmount) && isset($g->fareInfoGroup->surchargesGroup->taxesAmount->taxDetails)) {
                foreach ($this->iterateStd($g->fareInfoGroup->surchargesGroup->taxesAmount->taxDetails) as $d) {
                    $tax = Money::fromString((string)(floatval((string)$d->rate) * $personsCount), $totalFares[712]->getCurrency());
                    $totalTaxes[(string)$d->type] = isset($totalTaxes[(string)$d->type]) ? $tax->add($totalTaxes[(string)$d->type]) : $tax;
                }
            }

            //Iterate segments for first passenger group TODO: Find out the right way
            //Maybe skipt it, because pricePNR does that
            /*
            if ($groupNumber++ == 0)
                foreach ($this->iterateStd($g->fareInfoGroup->segmentLevelGroup) as $b) {
                    $baggageAllowance = $b->baggageAllowance->baggageDetails;
                    $classOfService = (string)$b->cabinGroup->cabinSegment->bookingClassDetails->designator;
                    $bagAllowance = new BagAllowance(
                        isset($baggageAllowance->freeAllowance) ? floatval((string)$baggageAllowance->freeAllowance) * $personsCount : null,
                        isset($baggageAllowance->quantityCode) ? (string)$baggageAllowance->quantityCode : null
                    );
                    $segmentDetails[] = new SegmentDetails($classOfService, $bagAllowance);
                }
            */
        }

        /** @var Money $totalFare */
        $totalFare = $totalFares[712];

        /** @var Money $baseFare */
        $baseFare = $totalFares['B'];

        /** @var Money $tax */
        $tax = $totalFare->subtract($baseFare);

        $price = new Price($baseFare, $tax);

        //Find commissions
        $commissions = $this->getClient()->getCommissions($orderFlow->getSegments(), $orderFlow->getValidatingCarrier(), $orderFlow->getSearchRequest());
        if ($commissions == null) {
            throw new \Exception("No commissions found");
        }

        //Calculate
        $commission = $faresPerPassengers->calculateCommission($commissions);
        $price->setCommission($commission);

        $orderFlow->setPrice($price);
        $orderFlow->setCommissions($commissions);

        $orderFlow->setIsPublishedFare($isPublishedFare);
    }

    /**
     * @return Fare_InformativePricingWithoutPNRRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
