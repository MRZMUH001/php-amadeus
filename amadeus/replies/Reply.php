<?php

namespace Amadeus\replies;

abstract class Reply
{
    /** @var \SimpleXMLElement */
    private $_xml;

    /**
     * Response constructor.
     * @param $answer
     */
    public function __construct($answer)
    {
        $answer = str_replace('soap:', '', $answer);

        $className = get_called_class();

        $this->_xml = simplexml_load_string($answer)->$className;
    }

    public function xpath()
    {

    }

}