<?php

namespace amadeus\requests;

use amadeus\Client;
use amadeus\replies\Reply;

abstract class Request
{
    abstract public function send(Client $client);

    /**
     * Sends request and returns response object.
     *
     * @param Client $client
     * @param string $actionName
     * @param array  $params
     * @param string $class
     *
     * @return Reply
     */
    protected function innerSend(Client $client, $actionName, $params, $class)
    {
        $data = $client->getClient()->soapCall($actionName, [$actionName => $params]);

        $result = new $class($client, $this, $data);

        return $result;
    }
}
