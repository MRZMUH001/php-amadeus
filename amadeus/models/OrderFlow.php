<?php

namespace Amadeus\models;

class OrderFlow
{
    /** @var FlightSegmentCollection */
    private $_segments;

    /** @var string */
    private $_validatingCarrier;

    /** @var SimpleSearchRequest */
    private $_searchRequest;

    private $_pnr;

    /** @var Price */
    private $_price;

    /** @var  PassengerCollection */
    private $_passengers;

    /** @var \DateTime */
    private $_lastTktDate;

    /** @var string */
    private $_rules;

    /** @var string */
    private $_clientEmail;

    /** @var string */
    private $_clientPhone;

    /** @var bool */
    private $_isPublishedFare;

    /** @var  AgentCommissions */
    private $_commissions;

    /** @var  string */
    private $_providerName;

    /** @var  string */
    private $_partner;

    /** @var  string */
    private $_marker;

    /** @var  string */
    private $_locale;

    /** @var  string Unique id */
    private $_orderReference;

    /** @var string[] */
    private $_cabins = [];

    /** @var  string */
    private $_additionalPnrNumber;

    /**
     * @return string
     */
    public function getAdditionalPnrNumber()
    {
        return $this->_additionalPnrNumber;
    }

    /**
     * @param string $additionalPnrNumber
     */
    public function setAdditionalPnrNumber($additionalPnrNumber)
    {
        $this->_additionalPnrNumber = $additionalPnrNumber;
    }

    /**
     * @return \string[]
     */
    public function getCabins()
    {
        return $this->_cabins;
    }

    /**
     * @param \string[] $cabins
     */
    public function setCabins($cabins)
    {
        $this->_cabins = $cabins;
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->_orderReference;
    }

    /**
     * @param string $orderReference
     */
    public function setOrderReference($orderReference)
    {
        $this->_orderReference = $orderReference;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * @return string
     */
    public function getPartner()
    {
        return $this->_partner;
    }

    /**
     * @param string $partner
     */
    public function setPartner($partner)
    {
        $this->_partner = $partner;
    }

    /**
     * @return string
     */
    public function getMarker()
    {
        return $this->_marker;
    }

    /**
     * @param string $marker
     */
    public function setMarker($marker)
    {
        $this->_marker = $marker;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->_providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->_providerName = $providerName;
    }

    /**
     * @return FlightSegmentCollection
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * @param FlightSegmentCollection $segments
     */
    public function setSegments($segments)
    {
        $this->_segments = $segments;
    }

    /**
     * @return string
     */
    public function getValidatingCarrier()
    {
        return $this->_validatingCarrier;
    }

    /**
     * @param string $validatingCarrier
     */
    public function setValidatingCarrier($validatingCarrier)
    {
        $this->_validatingCarrier = $validatingCarrier;
    }

    /**
     * @return SimpleSearchRequest
     */
    public function getSearchRequest()
    {
        return $this->_searchRequest;
    }

    /**
     * @param SimpleSearchRequest $searchRequest
     */
    public function setSearchRequest($searchRequest)
    {
        $this->_searchRequest = $searchRequest;
    }

    /**
     * @return mixed
     */
    public function getPnr()
    {
        return $this->_pnr;
    }

    /**
     * @param mixed $pnr
     */
    public function setPnr($pnr)
    {
        $this->_pnr = $pnr;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * @param Price $price
     */
    public function setPrice($price)
    {
        $this->_price = $price;
    }

    /**
     * @return PassengerCollection
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * @param PassengerCollection $passengers
     */
    public function setPassengers($passengers)
    {
        $this->_passengers = $passengers;
    }

    /**
     * @return \DateTime
     */
    public function getLastTktDate()
    {
        return $this->_lastTktDate;
    }

    /**
     * @param \DateTime $lastTktDate
     */
    public function setLastTktDate($lastTktDate)
    {
        $this->_lastTktDate = $lastTktDate;
    }

    /**
     * @return string
     */
    public function getRules()
    {
        return $this->_rules;
    }

    /**
     * @param string $rules
     */
    public function setRules($rules)
    {
        $this->_rules = $rules;
    }

    /**
     * @return string
     */
    public function getClientEmail()
    {
        return $this->_clientEmail;
    }

    /**
     * @param string $clientEmail
     */
    public function setClientEmail($clientEmail)
    {
        $this->_clientEmail = $clientEmail;
    }

    /**
     * @return string
     */
    public function getClientPhone()
    {
        return $this->_clientPhone;
    }

    /**
     * @param string $clientPhone
     */
    public function setClientPhone($clientPhone)
    {
        $this->_clientPhone = $clientPhone;
    }

    /**
     * @return boolean
     */
    public function isIsPublishedFare()
    {
        return $this->_isPublishedFare;
    }

    /**
     * @param boolean $isPublishedFare
     */
    public function setIsPublishedFare($isPublishedFare)
    {
        $this->_isPublishedFare = $isPublishedFare;
    }

    /**
     * @return AgentCommissions
     */
    public function getCommissions()
    {
        return $this->_commissions;
    }

    /**
     * @param AgentCommissions $commissions
     */
    public function setCommissions($commissions)
    {
        $this->_commissions = $commissions;
    }
}
