<?php

namespace Amadeus\requests;


use Amadeus\Client;
use Amadeus\replies\Ticket_CreateTSTFromPricingReply;

class Ticket_CreateTSTFromPricingRequest extends Request
{
    /** @var  int */
    private $_faresCount;

    /**
     * @return int
     */
    public function getFaresCount()
    {
        return $this->_faresCount;
    }

    /**
     * @param int $faresCount
     */
    public function setFaresCount($faresCount)
    {
        $this->_faresCount = $faresCount;
    }

    /**
     * @param Client $client
     * @return Ticket_CreateTSTFromPricingReply
     * @throws \Exception
     */
    public function send(Client $client)
    {
        if ($this->_faresCount == null)
            throw new \Exception("Fare count not set");

        $params = [];
        for ($i = 1; $i <= $this->_faresCount; $i++) {
            $params['psaList'][$i]['itemReference']['referenceType'] = 'TST';
            $params['psaList'][$i]['itemReference']['uniqueReference'] = $i;
        }

        return $this->innerSend($client, 'Ticket_CreateTSTFromPricing', $params, Ticket_CreateTSTFromPricingReply::class);
    }
}