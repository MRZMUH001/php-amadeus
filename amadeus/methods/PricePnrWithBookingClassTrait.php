<?php

namespace Amadeus\Methods;

use Amadeus\models\BagAllowance;
use Amadeus\models\SegmentDetails;
use Amadeus\models\TicketDetails;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;

trait PricePnrWithBookingClassTrait
{

    use BasicMethodsTrait;

    /**
     * Show details on price & baggage
     *
     * @param string $currency
     * @return TicketDetails
     */
    public function pricePnrWithBookingClass($currency)
    {
        $data = $this->getClient()->farePricePNRWithBookingClass($currency);

        $fareList = [];
        foreach ($data->fareList->fareDataInformation as $f) {
            if (isset($f->fareAmount))
                $fareList[(string)$f->fareDataQualifier] = Money::fromString(
                    (string)$f->fareAmount,
                    new Currency((string)$f->fareCurrency)
                );
        }

        $taxesList = [];
        foreach ($this->iterateStd($data->fareList->taxInformation) as $t) {
            $taxesList[] = Money::fromString(
                (string)$t->amountDetails->fareDataMainInformation->fareAmount,
                new Currency((string)$t->amountDetails->fareDataMainInformation->fareCurrency)
            );
        }

        $lastTktDate = \DateTime::createFromFormat('d-m-Y',
            join('-', [
                $data->fareList->lastTktDate->dateTime->day,
                $data->fareList->lastTktDate->dateTime->month,
                $data->fareList->lastTktDate->dateTime->year
            ])
        );

        $segments = [];
        foreach ($data->fareList->segmentInformation as $s) {
            $classOfService = $s->segDetails->segmentDetail->classOfService;
            $bagAllowanceInformation = $s->bagAllowanceInformation->bagAllowanceDetails;
            $bagAllowance = new BagAllowance(
                isset($bagAllowanceInformation->baggageWeight) ? (string)$bagAllowanceInformation->baggageWeight : null,
                (string)$bagAllowanceInformation->baggageType,
                isset($bagAllowanceInformation->measureUnit) ? (string)$bagAllowanceInformation->measureUnit : null,
                isset($bagAllowanceInformation->baggageQuantity) ? (string)$bagAllowanceInformation->baggageQuantity : null
            );
            $segments = new SegmentDetails($classOfService, $bagAllowance);
        }

        return new TicketDetails($fareList, $taxesList, $segments, $lastTktDate);
    }

}