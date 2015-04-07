<?php

namespace Amadeus\requests;

use Amadeus\Client;
use Amadeus\replies\PNR_RetrieveReply;

class PNR_RetrieveRequest extends Request
{

    private $_pnrNumber;

    /**
     * @return mixed
     */
    public function getPnrNumber()
    {
        return $this->_pnrNumber;
    }

    /**
     * @param mixed $pnrNumber
     */
    public function setPnrNumber($pnrNumber)
    {
        $this->_pnrNumber = $pnrNumber;
    }

    /**
     * @param Client $client
     * @return PNR_RetrieveReply
     * @throws \Exception
     */
    public function send(Client $client)
    {
        if ($this->_pnrNumber == null)
            throw new \Exception("PNR not specified");

        $params = [];
        $params['retrievalFacts']['retrieve']['type'] = 2;
        $params['retrievalFacts']['reservationOrProfileIdentifier']['reservation']['controlNumber'] = $this->_pnrNumber;

        return $this->innerSend($client, 'PNR_Retrieve', $params, PNR_RetrieveReply::class);
    }
}