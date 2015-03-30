<?php

namespace Amadeus;

use Amadeus\Methods\FareRulesTrait;
use Amadeus\Methods\InformativePricingWithoutPnrTrait;
use Amadeus\Methods\PricePnrWithBookingClassTrait;
use Amadeus\Methods\SearchTicketsMethodTrait;
use Amadeus\Methods\SellFromRecommendationTrait;

class Client
{
    //Tickets search
    use SearchTicketsMethodTrait;

    //Sell from recommendation
    use SellFromRecommendationTrait;

    //TODO:
    use PricePnrWithBookingClassTrait;

    use InformativePricingWithoutPnrTrait;

    use FareRulesTrait;
}