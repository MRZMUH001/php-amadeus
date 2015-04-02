<?php

namespace Amadeus\models;


class PassengerCollection
{

    /**
     * @var Passenger[]
     */
    private $_passengers = [];

    /**
     * Maximum current index
     * @var int
     */
    private $_maxIndex = 1;

    /**
     * @return Passenger[]
     */
    public function getPassengers()
    {
        return $this->_passengers;
    }

    /**
     * @param Passenger $passenger
     */
    public function addPassenger($passenger)
    {
        //Set index
        if ($passenger->getType() != 'I')
            $passenger->setIndex($this->_maxIndex++);

        //If infant, attach him to some adult
        if ($passenger->getType() == 'I') {
            foreach ($this->getAdults() as $adult)
                if ($adult->getAssociatedInfant() == null) {
                    $adult->setAssociatedInfant($passenger);
                    break;
                }
        }

        $this->_passengers[] = $passenger;
    }

    /**
     * @return Passenger[]
     */
    public function getAdults()
    {
        return array_filter($this->_passengers, function (Passenger $passenger) {
            return $passenger->getType() == 'A';
        });
    }

    /**
     * @return Passenger[]
     */
    public function getChildren()
    {
        return array_filter($this->_passengers, function (Passenger $passenger) {
            return $passenger->getType() == 'C';
        });
    }

}