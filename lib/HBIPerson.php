<?php
namespace HBI;

/**
*
*/
class HBIPerson
{
    private $_name;
    private $_surname;
    private $_country;
    private $_postal;
    private $_address;
    private $_city;
    private $_province;
    private $_gender;
    private $_birthday;
    private $_phone;
    private $_mobile;
    private $_card;
    private $_cvv;

    /**
     * [__construct description]
     */
    function __construct()
    {
        # nothign to do
    }


    public function person() {}

    protected function setName($name) { $this->_name = $name; }
    public function getName() { return $this->_name; }

    protected function setSurname($surname) { $this->_surname = $surname; }
    public function getSurname() { return $this->_surname; }

    protected function setCountry($country) { $this->_country = $country; }
    public function getCountry() { return $this->_country; }

    protected function setPostal($postal) { $this->_postal = $postal; }
    public function getPostal() { return $this->_postal; }

    protected function setAddress($address) { $this->_address = $address; }
    public function getAddress() { return $this->_address; }

    protected function setCity($city) { $this->_city = $city; }
    public function getCity() { return $this->_city; }

    protected function setProvince($province) { $this->_province = $province; }
    public function getProvince() { return $this->_province; }

    protected function setGender($gender) { $this->_gender = $gender; }
    public function getGender() { return $this->_gender; }

    protected function setBirthday($birthday) { $this->birthday = $birthday; }
    public function getBirthday() { return $this->_birthday; }

    protected function setPhone($phone) { $this->_phone = $phone; }
    public function getPhone() { return $this->_phone; }

    protected function setMobile($mobile) { $this->_mobile = $mobile; }
    public function getMobile() { return $this->_mobile; }

    protected function setCard($card) { $this->_card = $card; }
    public function getCard() { return $this->_card; }

    protected function setCvv($cvv) { $this->cvv = $cvv; }
    public function getCvv() { return $this->cvv; }

    function setAttributes($attributes) {
        $this->setName($attributes->first_name);
        $this->setSurname($attributes->last_name);
        $this->setCountry($attributes->country);
        $this->setPostal($attributes->postal_code);
        $this->setAddress($attributes->address);
        $this->setCity($attributes->city);
        $this->setProvince($attributes->province);
        $this->setGender($attributes->gender);
        $this->setBirthday($attributes->birthday);
        $this->setPhone($attributes->phone);
        $this->setMobile($attributes->mobile);
        $this->setCard($attributes->credit_card);
        $this->setCvv($attributes->cvv);
    }

}
