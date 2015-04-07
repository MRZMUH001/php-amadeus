<?php

namespace Amadeus\requests;

use Amadeus\Client;
use Amadeus\replies\Fare_PricePNRWithBookingClassReply;

class Fare_PricePNRWithBookingClassRequest extends Request
{
    /** @var string */
    private $_currency;

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->_currency = $currency;
    }

    /**
     * @param Client $client
     *
     * @return Fare_PricePNRWithBookingClassReply
     *
     * @throws \Exception
     */
    public function send(Client $client)
    {
        if ($this->_currency == null) {
            throw new \Exception("Currency not set");
        }

        $params = [];

        //Currency override
        $params['pricingOptionGroup'][0] = [
            'pricingOptionKey' => [
                'pricingOptionKey' => 'FCO',
            ],
            'currency' => [
                'firstCurrencyDetails' => [
                    'currencyQualifier' => 'FCO',
                    'currencyIsoCode' => $this->_currency,
                ],
            ],
        ];

        //Published fares
        $params['pricingOptionGroup'][1]['pricingOptionKey']['pricingOptionKey'] = 'RP';

        //Unifares
        $params['pricingOptionGroup'][2]['pricingOptionKey']['pricingOptionKey'] = 'RU';

        return $this->innerSend($client, "Fare_PricePNRWithBookingClass", $params, Fare_PricePNRWithBookingClassReply::class);
    }
}
