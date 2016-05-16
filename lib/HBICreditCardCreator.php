<?php
namespace HBI;

use HBI\HBICreditCards;

/**
*
*/
class HBICreditCardCreator
{
    private $_roots;
    private $_cards;

    function __construct()
    {
        $this->setCardRootNumberValues();
        $this->_cards = array();
    }

    function __destruct()
    {
        unset($this->_cards);
        unset($this->_roots);
    }

    /**
     * [generate description]
     * @param  [type]  $cardType [description]
     * @param  integer $results  [description]
     * @return [type]            [description]
     */
    public function generate($cardType, $genQty = 1){
        for($i = 0; $i < $genQty; $i++) {

            $cc       = new HBICreditCard;
            $cc->type = $cardType;

            $rndNumbers = SELF::createRandomNumberString(15);
            $rootDigits = SELF::getRootNumbersByCardType($cc);
            $rndNumbers = substr_replace($rndNumbers, $rootDigits, 0, strlen($rootDigits));

            $cc->number = SELF::setCheckDigit($rndNumbers);
            $cc->cvv    = SELF::createCvv(
                            $cc->type == "american" ? true : false
                          );

            $cc->expiration = SELF::createExpireDate();
            $this->_cards[] = $cc;
        }

        if($genQty == 1) {
            return $this->_cards[0];
        }

        return $this->_cards;
    }

    /**
     * [setCardRootNumberValues description]
     */
    private function setCardRootNumberValues()
    {
        $this->_roots = array(
            'visa'     => array('4'),
            'master'   => array('51', '52', '53', '54', '55'),
            'diners'   => array('36', '36'),
            'discover' => array('6011', '65'),
            'jcb'      => array('35'),
            'american' => array('34', '37')
        );
    }

    /**
     * [getRootNumbersByCardType description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function getRootNumbersByCardType(HBICreditCard $cc)
    {
        if($cc->type == 'random') {
            $idx      = array_rand($this->_roots);
            $cc->type = $idx;
        }

        $rootDigits = $this->_roots[$cc->type];

        $idx        = rand((int) $rootDigits,count($rootDigits)) -1;
        $rootDigits = $rootDigits[$idx];

        return $rootDigits;
    }

  /**
     * [getRootNumbersByCardType2 description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    private function getRootNumbersByCardType2(HBICreditCard $card)
    {
        if($card->type == 'random') {
            $idx        = array_rand($this->_roots);

            $rootDigits = $this->_roots[$idx];
        } else {
            $rootDigits = $this->_roots[$card->type];
        }

        $idx        = rand((int) $rootDigits,count($rootDigits)) -1;
        $rootDigits = $rootDigits[$idx];

        return $rootDigits;
    }

    /**
     * [convertToSumOfMultiplication description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    private function convertToSumOfMultiplication($value){
        if($value > 4){
            $value = ($value * 2) - 10 + 1;
        }else{
            $value *= 2;
        }
        return $value;
    }

    /**
     * [setCheckDigit description]
     * @param [type] $string [description]
     */
    private function setCheckDigit($string){
        $oddPositions = 0;
        $evenPositions = 0;
        for($x =0; $x < strlen($string); $x++){
            if($x % 2 == 0){
                $oddPositions += SELF::convertToSumOfMultiplication($string[$x]);
            }else{
                $evenPositions += $string[$x];
            }
        }
        $lastDigit = (10 - (($oddPositions + $evenPositions) % 10)) % 10;
        $string[strlen($string)] = $lastDigit;
        return $string;
    }

    /**
     * [createRandomNumberString description]
     * @param  [type] $length [description]
     * @return [type]         [description]
     */
    private function createRandomNumberString($length)
    {
        $characters = '0123456789';
        $rndstring  = '';
        for ($i = 0; $i < $length; $i++) {
            $rndstring .= $characters[rand(0, strlen($characters) -1)];
        }
        return $rndstring;
    }

    private function createCvv($isException = false)
    {
        if($isException) {
            return rand(1111,9999);
        } else {
            return rand(111,999);
        }
    }

    private function createExpireDate($isExpired = false)
    {
        $exp = SELF::randomDate("2016-01-01", "2022-01-01");
        print_r($exp);

        return $exp;
    }

    // TODO: Move this to HBIHelper
    function randomDate($start_date, $end_date)
    {
        // Convert to timetamps
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d', $val);
    }
}
// include_once('HBIBasicObject.php');
// include_once('HBICreditCard.php');
