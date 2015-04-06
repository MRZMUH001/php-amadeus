<?php

namespace Amadeus\requests;

use Amadeus\InnerClient;
use Amadeus\responses\Response;

abstract class Request
{

    abstract function send(InnerClient $client);

    /**
     * Sends request and returns response object
     *
     * @param InnerClient $client
     * @param string $actionName
     * @param array $params
     * @param string $class
     *
     * @return Response
     */
    protected function innerSend(InnerClient $client, $actionName, $params, $class)
    {
        $data = $client->soapCall($actionName, [$actionName => $params]);

        $result = new $class($data);

        return $result;
    }

}