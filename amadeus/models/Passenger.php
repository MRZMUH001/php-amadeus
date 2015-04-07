<?php

namespace amadeus\models;

use DateTime;

class Passenger
{
    /**
     * Passenger type (A - adult, C - child, I - infant).
     *
     * @var string
     */
    private $_type = 'A';

    /**
     * First name.
     *
     * @var string
     */
    private $_firstName;

    /**
     * Last name.
     *
     * @var string
     */
    private $_lastName;

    /**
     * Sex (m/f).
     *
     * @var string
     */
    private $_sex;

    /**
     * 3-char nationality code.
     *
     * @var string
     */
    private $_nationalityCode;

    /**
     * Passport number.
     *
     * @var string
     */
    private $_passport;

    /**
     * Birthday.
     *
     * @var DateTime
     */
    private $_birthday;

    /**
     * Document expiration date.
     *
     * @var DateTime
     */
    private $_documentExpiration;

    /**
     * @var int
     */
    private $_index;

    /**
     * @var Passenger|null
     */
    private $_associatedInfant;

    public function __construct($type, $firstName, $lastName, $sex, $nationalityCode, $passport, $birthday, $documentExpiration)
    {
        //TODO: Validate everything
        //Nationality - 3 char
        //Sex - M/F
        //Birthday - in past
        //Document expiration in future

        $this->_type = $type;
        $this->_firstName = $firstName;
        $this->_lastName = $lastName;
        $this->_sex = $sex;
        $this->_nationalityCode = $nationalityCode;
        $this->_passport = $passport;
        $this->_birthday = $birthday;
        $this->_documentExpiration = $documentExpiration;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->_firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * @return string
     */
    public function getSex()
    {
        return strtoupper($this->_sex);
    }

    /**
     * @return string
     */
    public function getNationalityCode()
    {
        return $this->_nationalityCode;
    }

    /**
     * @return string
     */
    public function getPassport()
    {
        return $this->_passport;
    }

    /**
     * @return DateTime
     */
    public function getBirthday()
    {
        return $this->_birthday;
    }

    /**
     * @return DateTime
     */
    public function getDocumentExpiration()
    {
        return $this->_documentExpiration;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Return name with code (MR/MRS).
     *
     * @return string
     */
    public function getFirstNameWithCode()
    {
        if ($this->getType() == 'A') {
            return $this->getFirstName().' '.($this->getSex() == 'F' ? 'MRS' : 'MR');
        } else {
            return $this->getFirstName();
        }
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->_index;
    }

    /**
     * @return Passenger|null
     */
    public function getAssociatedInfant()
    {
        return $this->_associatedInfant;
    }

    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->_index = $index;
    }

    /**
     * @param Passenger|null $associatedInfant
     */
    public function setAssociatedInfant($associatedInfant)
    {
        $this->_associatedInfant = $associatedInfant;
    }

    /**
     * @return string
     */
    public function ssrDocsText()
    {
        $data = [
            "P",
            $this->getNationalityCode(),
            $this->clearedPassport(),
            $this->getNationalityCode(),
            strtoupper($this->getBirthday()->format('dMy')),
            strtoupper($this->getSex()).($this->getType() == 'I' ? 'I' : ''),
            strtoupper($this->getDocumentExpiration()->format('dMy')),
            $this->getLastName(),
            $this->getFirstName(),
            "H",
        ];

        return implode("-", $data);
    }

    /**
     * Cleared passport number.
     *
     * @return mixed
     */
    public function clearedPassport()
    {
        $translit = iconv('UTF-8', 'ASCII//TRANSLIT', transliterator_transliterate('Cyrillic-Latin', $this->getPassport()));

        return preg_replace('/[^a-zA-Z\dà-ÿÀ-ß]+/', '', $translit);
    }
}
