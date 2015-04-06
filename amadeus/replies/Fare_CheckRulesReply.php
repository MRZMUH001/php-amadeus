<?php

namespace Amadeus\replies;


use Amadeus\requests\Fare_CheckRulesRequest;

class Fare_CheckRulesReply extends Reply
{

    /**
     * Rules text
     *
     * @return string
     */
    public function getText()
    {
        $rules = [];
        foreach ($this->iterateStd($this->xml()->tariffInfo->fareRuleText) as $item)
            $rules[] = (string)$item->freeText;

        return join("\n", $rules);
    }

    /**
     * @return Fare_CheckRulesRequest
     */
    function getRequest()
    {
        return $this->_request;
    }
}