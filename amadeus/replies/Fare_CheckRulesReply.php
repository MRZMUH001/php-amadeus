<?php

namespace amadeus\replies;

use amadeus\requests\Fare_CheckRulesRequest;

class Fare_CheckRulesReply extends Reply
{
    /**
     * Rules text.
     *
     * @return string
     */
    public function getText()
    {
        $rules = [];
        foreach ($this->iterateStd($this->xml()->tariffInfo->fareRuleText) as $item) {
            $rules[] = (string) $item->freeText;
        }

        return implode("\n", $rules);
    }

    /**
     * @return Fare_CheckRulesRequest
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
