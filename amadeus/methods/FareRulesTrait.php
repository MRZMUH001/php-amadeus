<?php

namespace Amadeus\methods;

trait FareRulesTrait
{
    use BasicMethodsTrait;

    /**
     * Return rules text.
     *
     * @return string
     */
    public function getFareRules()
    {
        $rulesData = $this->getClient()->checkRules();
        $rules = [];
        foreach ($this->iterateStd($rulesData->tariffInfo->fareRuleText) as $item) {
            $rules[] = (string) $item->freeText;
        }

        return implode("\n", $rules);
    }
}
