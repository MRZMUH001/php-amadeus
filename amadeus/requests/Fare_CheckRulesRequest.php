<?php

namespace Amadeus\requests;


use Amadeus\Client;
use Amadeus\replies\Fare_CheckRulesReply;

class Fare_CheckRulesRequest extends Request
{

    /**
     * @param Client $client
     *
     * @return Fare_CheckRulesReply
     */
    function send(Client $client)
    {
        $params = [];
        $params['msgType']['messageFunctionDetails']['messageFunction'] = 712;
        $params['itemNumber']['itemNumberDetails']['number'] = 1;
        $params['fareRule']['tarifFareRule']['ruleSectionId'] = 'PE';

        return $this->innerSend($client, 'Fare_CheckRules', $params, Fare_CheckRulesReply::class);
    }
}