<?php

namespace Amadeus\Methods;

use Amadeus\models\FlightSegment;
use Amadeus\models\FlightSegmentCollection;
use Amadeus\models\SimpleSearchRequest;
use Amadeus\models\TicketPrice;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;
use SimpleXMLElement;


trait SearchTicketsMethodTrait
{

    use BasicMethodsTrait;

    /**
     * Search for flights
     * @param SimpleSearchRequest $searchRequest
     * @return TicketPrice[]
     */
    public function searchTickets(SimpleSearchRequest $searchRequest)
    {
        $travellers = [
            'A' => $searchRequest->getAdults(),
            'C' => $searchRequest->getChildren(),
            'I' => $searchRequest->getInfants()
        ];

        $data = $this->_ws->fareMasterPricerTravelBoardSearch(
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
                        (string)$fi->flightNumber,
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

                    $flightSegmentsCollection->addSegment($segment);
                }

                $flights[$flightIndex] = $flightSegmentsCollection;
            }
        }

        //Parse recomendations
        foreach ($data->recommendation as $recommendation) {
            //Get prices
            $priceTax = Money::fromString((string)$recommendation->paxFareProduct->paxFareDetail->totalTaxAmount, new Currency($currency));
            $priceFare = Money::fromString((string)$recommendation->paxFareProduct->paxFareDetail->totalFareAmount, new Currency($currency));

            $blankCount = 1;//count($recommendation->xpath(".//traveller"));//TODO

            $cabins = [];
            $bookingClasses = [];
            $availabilities = [];
            $fareBasis = [];
            $publishedFare = true;

            if ((string)$recommendation->paxFareProduct->paxReference->ptc == 'ADT')
                foreach ($this->iterateStd($recommendation->paxFareProduct->fareDetails->groupOfFares) as $fare) {
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
            foreach ($this->IterateStd($recommendation->paxFareProduct->paxFareDetail->codeShareDetails) as $codeShare) {
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
            foreach ($this->iterateStd($recommendation->paxFareProduct->fareDetails->groupOfFares) as $fare)
                $additionalInfo .= "\n\nFare basis: " . (string)$fare->productInformation->fareProductDetail->fareBasis;

            $results[] = new TicketPrice(
                $blankCount,
                $priceFare,
                $priceTax,
                $segments,
                $validatingCarrierIata,
                $marketingCarrierIatas,
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