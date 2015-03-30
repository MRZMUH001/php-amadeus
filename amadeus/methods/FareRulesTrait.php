<?php

namespace Amadeus\Methods;


trait FareRulesTrait
{

    use BasicMethodsTrait;

    public function getFareRules()
    {
        return $this->_ws->checkRules();
    }

}