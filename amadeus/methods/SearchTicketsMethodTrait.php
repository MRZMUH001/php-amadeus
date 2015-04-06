<?php

namespace Amadeus\Methods;

use Amadeus\models\FlightSegment;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\Recommendation;
use Amadeus\models\SimpleSearchRequest;
use common\models\Price;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;
use SimpleXMLElement;


trait SearchTicketsMethodTrait
{

    use BasicMethodsTrait;

    /**
     * Search for flights
     * @param SimpleSearchRequest $searchRequest
     * @return Recommendation[]
     */
    public function searchTickets(SimpleSearchRequest $searchRequest)
    {
        $travellers = [
            'A' => $searchRequest->getAdults(),
            'C' => $searchRequest->getChildren(),
            'I' => $searchRequest->getInfants()
        ];

        $data = $this->getClient()->fareMasterPricerTravelBoardSearch(
            $searchRequest->getDate()->format('dmy'),
            $searchRequest->getOrigin(),
            $searchRequest->getDestination(),
            $travellers,
            $searchRequest->getDateReturn() == null ? null : $searchRequest->getDateReturn()->format('dmy'),
            $searchRequest->getLimit(),
            $searchRequest->getCurrency(),
            $searchRequest->getCabin()
        );

        $currency = (string)$data->conversionRate->conversionRateDetail->currency;

        $results = [];

        //Parse flights

        /** @var FlightSegmentCollection[] $flights */
        $flights = [];

        foreach ($this->iterateStd($data->flightIndex) as $i => $flightIndex) {
            foreach ($flightIndex->groupOfFlights as $group) {
                //Find flight index
                $flightIndex = 0;
                foreach ($this->iterateStd($group->propFlightGrDetail->flightProposal) as $proposal)
                    if (!isset($proposal->unitQualifier)) {
                        $flightIndex = intval($proposal->ref);
                        break;
                    }

                $flightSegmentsCollection = new FlightSegmentCollection();

                //Find flight time
                /** @var SimpleXMLElement $group */
                foreach ($this->iterateStd($group->propFlightGrDetail->flightProposal) as $proposal)
                    if (isset($proposal->unitQualifier) && $proposal->unitQualifier == 'EFT')
                        $flightSegmentsCollection->setEstimatedFlightTime($this->convertAmadeusDurationToMinutes(intval($proposal->ref)));

                //Parse flight details
                foreach ($this->iterateStd($group->flightDetails) as $flightDetails) {
                    $fi = $flightDetails->flightInformation;

                    $segment = new FlightSegment(
                        isset($fi->companyId->operatingCarrier) ? (string)$fi->companyId->operatingCarrier : "",
                        (string)$fi->companyId->marketingCarrier,
                        (string)$fi->location[0]->locationId,
                        (string)$fi->location[1]->locationId,
                        isset($fi->flightNumber) ? (string)$fi->flightNumber : (string)$fi->flightOrtrainNumber,
                        $this->convertAmadeusDate((string)$fi->productDateTime->dateOfArrival),
                        $this->convertAmadeusTime((string)$fi->productDateTime->timeOfArrival),
                        $this->convertAmadeusDate((string)$fi->productDateTime->dateOfDeparture),
                        $this->convertAmadeusTime((string)$fi->productDateTime->timeOfDeparture)
                    );

                    if (isset($fi->location[0]->terminal))
                        $segment->setDepartureTerm((string)$fi->location[0]->terminal);

                    if (isset($fi->location[1]->terminal))
                        $segment->setArrivalTerm((string)$fi->location[1]->terminal);

                    $segment->setEquipmentTypeIata((string)$fi->productDetail->equipmentType);

                    //TODO: Set technical stops

                    $flightSegmentsCollection->addSegment($segment);
                }

                $flights[$flightIndex] = $flightSegmentsCollection;
            }
        }

        //Parse recommendations
        foreach ($data->recommendation as $recommendation) {
            //Get prices
            $priceTotal = Money::fromString((string)$recommendation->recPriceInfo->monetaryDetail[0]->amount, new Currency($currency));
            $priceTax = Money::fromString((string)$recommendation->recPriceInfo->monetaryDetail[1]->amount, new Currency($currency));
            $priceFare = $priceTotal->subtract($priceTax);

            $blankCount = 1;//count($recommendation->xpath(".//traveller"));//TODO

            $cabins = [];
            $bookingClasses = [];
            $availabilities = [];
            $fareBasis = [];
            $publishedFare = true;

            foreach ($this->iterateStd($recommendation->paxFareProduct) as $fp)
                if ((string)$fp->paxReference->ptc == 'ADT')
                    foreach ($this->iterateStd($fp->fareDetails->groupOfFares) as $fare) {
                        $cabins[] = (string)$fare->productInformation->cabinProduct->cabin;
                        $bookingClasses[] = (string)$fare->productInformation->cabinProduct->rbd;
                        $availabilities[] = (string)$fare->productInformation->cabinProduct->avlStatus;
                        $fareBasis[] = (string)$fare->productInformation->fareProductDetail->fareBasis;

                        $fareTypes = $fare->productInformation->fareProductDetail->fareType;
                        if (is_string($fareTypes))
                            $isPublished = $fareTypes == 'RP'; else
                            $isPublished = array_search('RP', $fareTypes) !== false;

                        $publishedFare = $publishedFare && $isPublished;
                    }

            $validatingCarrierIata = '';
            $marketingCarrierIatas = [];
            foreach ($this->iterateStd($recommendation->paxFareProduct) as $fp)
                foreach ($this->iterateStd($fp->paxFareDetail->codeShareDetails) as $codeShare) {
                    if (isset($codeShare->transportStageQualifier) && (string)$codeShare->transportStageQualifier == 'V')
                        $validatingCarrierIata = (string)$codeShare->company;

                    $marketingCarrierIatas[] = (string)$codeShare->company;
                }

            $lastTktDate = null;
            if (isset($recommendation->paxFareProduct))
                foreach ($this->iterateStd($recommendation->paxFareProduct) as $paxFareProduct)
                    if (isset($paxFareProduct->fare))
                        foreach ($paxFareProduct->fare as $fare)
                            if (isset($fare->pricingMessage->freeTextQualification->textSubjectQualifier) && $fare->pricingMessage->freeTextQualification->textSubjectQualifier == 'LTD') {
                                $description = (string)$fare->pricingMessage->freeTextQualification->description;
                                $matches = [];
                                preg_match('/\d/', $description, $matches);
                                //TODO
                                //last_tkt_date = Date . parse(last_tkt_date) if last_tkt_date
                            }

            $segments = [];
            foreach ($this->IterateStd($recommendation->segmentFlightRef) as $flightRef)
                foreach ($this->IterateStd($flightRef->referencingDetail) as $refDetail) {
                    if ($refDetail->refQualifier == 'S')
                        $segments = $flights[intval($refDetail->refNumber)];
                    break;
                }

            $additionalInfo = '';
            if (isset($recommendation->paxFareProduct->fare->pricingMessage->description))
                $additionalInfo = $recommendation->paxFareProduct->fare->pricingMessage->description;
            foreach ($this->iterateStd($recommendation->paxFareProduct) as $fp)
                foreach ($this->iterateStd($fp->fareDetails->groupOfFares) as $fare)
                    $additionalInfo .= "\n\nFare basis: " . (string)$fare->productInformation->fareProductDetail->fareBasis;

            $price = new Price($priceFare, $priceTax);

            $commissions = $this->getCommissions($segments, $validatingCarrierIata, $searchRequest);
            $commissions->apply($price, $searchRequest);

            $results[] = new Recommendation(
                $blankCount,
                $price,
                $segments,
                $validatingCarrierIata,
                array_unique($marketingCarrierIatas),
                $additionalInfo,
                $cabins,
                $bookingClasses,
                $availabilities,
                $lastTktDate,
                $fareBasis,
                $publishedFare
            );
        }

        return $results;
    }


}