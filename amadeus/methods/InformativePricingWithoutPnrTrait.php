<?php

namespace Amadeus\Methods;


use Amadeus\models\BagAllowance;
use Amadeus\models\SegmentDetails;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketDetails;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

trait InformativePricingWithoutPnrTrait
{

    use BasicMethodsTrait;

    /**
     * @param TicketDetails $ticketDetails
     * @param SimpleSearchRequest $request
     * @return TicketDetails
     */
    public function informativePricingWithoutPnr($ticketDetails, $request)
    {
        $data = $this->getClient()->fareInformativePricingWithoutPnr($ticketDetails, $request->getAdults(), $request->getInfants(), $request->getChildren(), $request->getCurrency());

        $totalFares = [];
        $totalTaxes = [];
        $segmentDetails = [];
        $isPublishedFare = $data->mainGroup->generalIndicatorsGroup->generalIndicators->priceTicketDetails->indicators = 'I';

        //Iterate via passenger groups
        $groupNumber = 0;
        foreach ($this->iterateStd($data->mainGroup->pricingGroupLevelGroup) as $g) {
            $personsCount = $g->numberOfPax->segmentControlDetails->numberOfUnits;

            //Fares
            foreach ($g->fareAmount->children() as $d) {
                $fare = Money::fromString(floatval((string)$d->amount) * $personsCount, new Currency((string)$d->currency));
                $totalFares[(string)$d->typeQualifier] = isset($totalFares[(string)$d->typeQualifier]) ? $fare->add($totalFares[(string)$d->typeQualifier]) : $fare;
            }

            //Taxes
            foreach ($this->iterateStd($g->surchargeGroup->taxesAmount->taxDetails) as $d) {
                $tax = Money::fromString(floatval((string)$d->rate) * $personsCount, new Currency($request->getCurrency()));;
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

        $ticketDetails->setFares($totalFares);
        $ticketDetails->setTaxes($totalTaxes);
        $ticketDetails->setSegmentDetails($segmentDetails);
        $ticketDetails->setIsPublishedFare($isPublishedFare);

        return $ticketDetails;
    }

}