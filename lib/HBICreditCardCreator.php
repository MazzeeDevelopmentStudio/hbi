<?php
namespace HBI;

use HBI\HBICreditCards;
use HBI\Exception\AutomationException;

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

            // For AMEX we only do 14+CheckDigit
            if($cc->type == "american") {
                $rndNumbers = substr($rndNumbers, 0, 14);
            }

            $cc->number = SELF::setCheckDigit($rndNumbers);
            $cc->cvv    = SELF::createCvv(
                            $cc->type == "american" ? true : false
                          );

            $cc->expiration = SELF::createExpireDate();
            $this->_cards[] = $cc;

            // error_log( sprintf('Credit Card: %s', print_r($cc,true)) );
        }

        if($genQty == 1) {
            return $this->_cards[0];
        }

        return $this->_cards;
    }

    public function generateSandboxCC($cardType, $genQty = 1)
    {
        $cList = $this->getSandboxCCList('recurly');

        for($i = 0; $i < $genQty; $i++) {
            $cc       = new HBICreditCard;
            $cTypeKey = $cardType == 'random' ? array_rand($cList) : $cardType;
            $cc->type = $cTypeKey;

            // checking if the card type has multiple numbers available
            if(is_array( $cList[$cTypeKey] )) {
                $cTypeSubKey = array_rand( $cList[$cTypeKey] );
                $cardNum     = $cList[ $cTypeKey ][$cTypeSubKey];
            } else {
                $cardNum = $cList[ $cTypeKey ];
            }

            $cc->number = $cardNum;
            $cc->cvv    = SELF::createCvv(
                            $cc->type == "American Express" ? true : false
                          );

            $cc->expiration = SELF::createExpireDate();
            $this->_cards[] = $cc;

        }

        print("CARD INFO: ".json_encode($this->_cards).PHP_EOL);

        if($genQty == 1) {
            return $this->_cards[0];
        }

        return $this->_cards;

    }

    private function getSandboxCCList($listType = 'recurly')
    {
        $ccList = array();

        $ccList['authnet'] = array(
            'American Express'=>'370000000000002',
            // 'Discover'=>        '6011000000000012',
            // 'JCB'=>             '3088000000000017',
            // 'Diners Club'=>     '38000000000006',
            // 'Carte Blanch'=>    '38000000000006',
            'Visa'=>array(
                                '4007000000027',
                                '4012888818888',
                                '4111111111111111',
                    ),
            'Master Card'=>      '5424000000000015'
        );
        
        $ccList['recurly'] = array(
            'American Express'=>array(
                '378282246310005',
                '371449635398431',
                '378734493671000'
            ),
            // We should check for wrong CC types
            // 'Diners Club'=>array(
            //     '30569309025904',
            //     '38520000023237'
            // ),
            // 'Discover'=>array(
            //     '6011111111111117',
            //     '6011000990139424'
            // ),
            // 'JCB'=>array(
            //     '3530111333300000',
            //     '3566002020360505'
            // ),
            'Master Card'=>array(
                '5555555555554444',
                '5105105105105100'
            ),
            'Visa'=>array(
                '4012888888881881',
                '4222222222222'
            )
        );

        return $ccList[$listType];
    }

    /**
     * [setCardRootNumberValues description]
     */
    private function setCardRootNumberValues()
    {
        $this->_roots = array(
            'visa'     => array('4'),
            'master'   => array('51', '52', '53', '54', '55'),
            'discover' => array('6011')
            // 'american' => array('34', '37')
            // 'diners'   => array('36', '36'),
            // 'jcb'      => array('35'),
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
        $now  = date('Y-m-d', time());
        $till = date('Y-m-d', strtotime('+10 years'));

        return SELF::randomDate($now, $till);
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
