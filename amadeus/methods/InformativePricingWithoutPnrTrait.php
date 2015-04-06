<?php

namespace Amadeus\Methods;


use Amadeus\models\BagAllowance;
use Amadeus\models\OrderFlow;
use Amadeus\models\SegmentDetails;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketDetails;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

trait InformativePricingWithoutPnrTrait
{

    use BasicMethodsTrait;

    /**
     * @param OrderFlow $orderFlow
     * @return OrderFlow
     */
    public function informativePricingWithoutPnr($orderFlow)
    {
        $data = $this->getClient()->fareInformativePricingWithoutPnr(
            $orderFlow->getSegments(),
            $orderFlow->getSearchRequest()->getAdults(),
            $orderFlow->getSearchRequest()->getInfants(),
            $orderFlow->getSearchRequest()->getChildren(),
            $orderFlow->getSearchRequest()->getCurrency()
        );

        $totalFares = [];
        $totalTaxes = [];
        $segmentDetails = [];
        $isPublishedFare = $data->mainGroup->generalIndicatorsGroup->generalIndicators->priceTicketDetails->indicators == 'I';

        //Iterate via passenger groups
        $groupNumber = 0;
        foreach ($this->iterateStd($data->mainGroup->pricingGroupLevelGroup) as $g) {
            $personsCount = $g->numberOfPax->segmentControlDetails->numberOfUnits;

            //Fares
            foreach ($g->fareInfoGroup->fareAmount as $d) {
                $fare = Money::fromString((string)(floatval((string)$d->amount) * $personsCount), new Currency((string)$d->currency));
                $totalFares[(string)$d->typeQualifier] = isset($totalFares[(string)$d->typeQualifier]) ? $fare->add($totalFares[(string)$d->typeQualifier]) : $fare;
            }

            //Taxes
            if (isset($g->fareInfoGroup->surchargesGroup->taxesAmount) && isset($g->fareInfoGroup->surchargesGroup->taxesAmount->taxDetails))
                foreach ($this->iterateStd($g->fareInfoGroup->surchargesGroup->taxesAmount->taxDetails) as $d) {
                    $tax = Money::fromString((string)(floatval((string)$d->rate) * $personsCount), new Currency($request->getCurrency()));;
                    $totalTaxes[(string)$d->type] = isset($totalTaxes[(string)$d->type]) ? $tax->add($totalTaxes[(string)$d->type]) : $tax;
                }

            //Iterate segments for first passenger group TODO: Find out the right way
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
        }

        /** @var Money $totalFare */
        $totalFare = $totalFares[712];

        /** @var Money $baseFare */
        $baseFare = $totalFares['B'];

        /** @var Money $tax */
        $tax = $totalFare->subtract($baseFare);

        $orderFlow->setPriceTax($tax);
        $orderFlow->setPriceFare($baseFare);

        $orderFlow->setIsPublishedFare($isPublishedFare);

        return $orderFlow;
    }

}