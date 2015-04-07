<?php

namespace Amadeus\models;

use DateTime;

class Recommendation
{
    /**
     * @var int
     */
    private $_blankCount;

    /**
     * @var Price
     */
    protected $_price;

    /**
     * Flight segments.
     *
     * @var FlightSegmentCollection
     */
    protected $_segments;

    /**
     * Validating carrier IATA.
     *
     * @var string
     */
    private $_validatingCarrierIata;

    /**
     * Marketing carrier IATAs.
     *
     * @var string[]
     */
    private $_suggestedMarketingCarrierIatas;

    /**
     * Some additional info.
     *
     * @var string
     */
    private $_additionalInfo = '';

    private $_cabins;
    private $_bookingClasses;
    private $_availabilities;

    /**
     * @var DateTime|null
     */
    private $_lastTktDate;
    private $_fareBasis;

    /**
     * Published fare?
     *
     * @var boolean
     */
    private $_isPublishedFare;

    /** @var string */
    private $_provider;

    /**
     * Create ticket price.
     *
     * @param int                     $blankCount
     * @param Price                   $price
     * @param FlightSegmentCollection $segments
     * @param string                  $validatingCarrierIata
     * @param string                  $suggestedMarketingCarrierIatas
     * @param string                  $additionalInfo
     * @param $cabins
     * @param $bookingClasses
     * @param $availabilities
     * @param DateTime|null           $lastTktDate
     * @param $fareBasis
     * @param boolean                 $isPublishedFare
     */
    public function __construct($blankCount, $price, FlightSegmentCollection $segments, $validatingCarrierIata, $suggestedMarketingCarrierIatas, $additionalInfo, $cabins, $bookingClasses, $availabilities, $lastTktDate, $fareBasis, $isPublishedFare)
    {
        $this->_blankCount = $blankCount;
        $this->_price = $price;
        $this->_segments = $segments;
        $this->_validatingCarrierIata = $validatingCarrierIata;
        $this->_suggestedMarketingCarrierIatas = $suggestedMarketingCarrierIatas;
        $this->_additionalInfo = $additionalInfo;
        $this->_cabins = $cabins;
        $this->_bookingClasses = $bookingClasses;
        $this->_availabilities = $availabilities;
        $this->_lastTktDate = $lastTktDate;
        $this->_fareBasis = $fareBasis;
        $this->_isPublishedFare = $isPublishedFare;
    }

    /**
     * @return int
     */
    public function getBlankCount()
    {
        return $this->_blankCount;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->_price;
    }

    /**
     * @return FlightSegmentCollection
     */
    public function getSegments()
    {
        return $this->_segments;
    }

    /**
     * @return string
     */
    public function getValidatingCarrierIata()
    {
        return $this->_validatingCarrierIata;
    }

    /**
     * @return \string[]
     */
    public function getSuggestedMarketingCarrierIatas()
    {
        return $this->_suggestedMarketingCarrierIatas;
    }

    /**
     * @return string
     */
    public function getAdditionalInfo()
    {
        return $this->_additionalInfo;
    }

    /**
     * @return mixed
     */
    public function getCabins()
    {
        return $this->_cabins;
    }

    /**
     * @return string[]
     */
    public function getBookingClasses()
    {
        return $this->_bookingClasses;
    }

    /**
     * @return mixed
     */
    public function getAvailabilities()
    {
        return $this->_availabilities;
    }

    /**
     * @return DateTime|null
     */
    public function getLastTktDate()
    {
        return $this->_lastTktDate;
    }

    /**
     * @return mixed
     */
    public function getFareBasis()
    {
        return $this->_fareBasis;
    }

    /**
     * @return boolean
     */
    public function isPublishedFare()
    {
        return $this->_isPublishedFare;
    }

    /**
     * Return provider name.
     *
     * @return string
     */
    public function getSource()
    {
        return 'amadeus';
    }

    /**
     * Departure IATA.
     *
     * @return string
     */
    public function getDepartureIata()
    {
        return $this->getSegments()->getFirstSegment()->getDepartureIata();
    }

    /**
     * Arrival IATA.
     *
     * @return string
     */
    public function getArrivalIata()
    {
        return $this->getSegments()->getLastSegment()->getArrivalIata();
    }

    /**
     * @param Price $price
     */
    public function setPrice($price)
    {
        $this->_price = $price;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->_provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->_provider = $provider;
    }

    /**
     * Serialize ticket offer.
     *
     * @return string
     */
    public function serialize()
    {
        $segmentCodes = implode(
            '-',
            array_map(
                function (FlightSegment $x) {
                    return $x->serialize();
                },
                $this->getSegments()->getSegments()
            )
        );

        $parts = [
            $this->getSource(),
            $this->getValidatingCarrierIata(),
            round($this->getPrice()->getTotalPrice()->getAmount() / 100),
            implode('', $this->getBookingClasses()),
            implode('', $this->getCabins()),
            implode('', $this->getAvailabilities()),
            $segmentCodes,
        ];

        return implode('.', $parts);
    }

    /**
     * Create ticket offer from recommendation hash.
     *
     * @param string $recommendation
     *
     * @return Recommendation
     */
    public static function deserialize($recommendation)
    {
        list($source, $validatingCarrierIata, $declaredPrice, $bookingClasses, $cabins, $availabilities, $segmentCodes) = explode('.', $recommendation);

        $segments = new FlightSegmentCollection();
        foreach (explode('-', $segmentCodes) as $segmentCode) {
            $segments->addSegment(FlightSegment::deserialize($segmentCode));
        }

        $recommendation = new Recommendation(
            1,
            null,
            null,
            $segments,
            $validatingCarrierIata,
            null,
            '',
            $cabins,
            $bookingClasses,
            $availabilities,
            null,
            null,
            true
        );

        $recommendation->setProvider($source);

        return $recommendation;
    }
}
