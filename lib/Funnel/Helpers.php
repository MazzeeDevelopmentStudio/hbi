<?php
namespace HBI\Funnel;

use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIPeople;
use HBI\HBIPerson;
use HBI\HBIAddresses;
use HBI\HBICreditCards;
use HBI\HBICreditCardCreator;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverActions;
use \Facebook\WebDriver\WebDriverSelect;
use \Facebook\WebDriver\WebDriverBy;

/**
*
*/
class Helpers
{
    private $_total;
    private $_items;

    public function __construct()
    {

    }


    public function totalAmountTally()
    {

    }

    public static function getBillingAddressDetails(HBIPerson $person)
    {
        $isBillingTheSame = (bool)rand(0,1);

        if($isBillingTheSame) {
            return $person;
        }

        $person = SElF::getPersonWithAddress();

        return $person;
    }

    public static function getShippingAddressDetails(HBIPerson $person)
    {
        $isBillingTheSame = (bool)rand(0,1);

        if($isBillingTheSame) {
            return $person;
        }

        $person = SElF::getPersonWithAddress();

        return $person;
    }

    public static function getPerson()
    {
        $ppl            = new HBIPeople();
        $person         = $ppl->buildCollection(1);
        $person->email  = HBIHelper::createRandomEmail(null, true);
        $person->phone  = SELF::createRandomPhoneNumber();

        return $person;
    }

    public static function getPersonWithAddress()
    {
        $person           = SELF::getPerson();
        $addr             = new HBIAddresses;
        $person->address  = $addr->buildCollection(1);
        $person->contact  = null;

        return $person;
    }

    public static function getPersonWithFullDetails()
    {
        $person        = SELF::getPersonWithAddress();
        $creator       = new HBICreditCardCreator;
        $person->card  = $creator->generate('random', 1);

        return $person;
    }

    public static function randomlySelectUpsells(HBIBrowser $browser)
    {
        $selector  = false ? 'input.responsive-img' : 'a.nothanks';
        $isPresent = WebDriverExpectedCondition::visibilityOfElementLocated(
                        WebDriverBy::cssSelector($selector)
                    );

        $browser->driver()->wait(20, 1000)->until(
            $isClickable = WebDriverExpectedCondition::elementToBeClickable(
                WebDriverBy::cssSelector($selector)
            )
        );

        // error_log( print_r( array($isPresent, $isClickable), true) );

        if($isPresent && $isClickable) {
            $browser->clickElement(
                WebDriverBy::cssSelector($selector)
            );
        }

    }

    public static function randomlySelectAddons(HBIBrowser $browser)
    {
        // OPT FOR ADDONS RANDOMALY
        $addons = $browser->driver()->findElements(
            WebDriverBy::cssSelector(".addon-checkbox.filled-in")
        );

        foreach ($addons as $addon) {
            if (rand(0, 1)) {
                $label = $browser->driver()->findElement(
                    WebDriverBy::cssSelector(
                        sprintf('label[for=%s]', $addon->getAttribute('id')) )
                );

                $label->getLocationOnScreenOnceScrolledIntoView();
                $label->click();
            }
        }
    }

    public static function fillOutOrderFormBilling($sequence="caacsp", HBIPerson $person, HBIBrowser $browser)
    {
        $method = sprintf( 'fillOut%s', strtoupper($sequence) );

        SELF::$method("billing", $person, $browser);
    }

    public static function fillOutOrderFormShipping($sequence="caacsp", HBIPerson $person, HBIBrowser $browser)
    {
        $method = sprintf( 'fillOut%s', strtoupper($sequence) );

        SELF::$method("shipping", $person, $browser);
    }

    private static function fillOutCAACSP($type="billing", HBIPerson $person, HBIBrowser $browser)
    {
        // optin-country_id
        $select = new WebDriverSelect(
            $browser->driver()->findElement(
                WebDriverBy::id("$type-country_id")
            )
        );
        $select->selectByVisibleText(strtoupper($person->address->country));

        $browser->addContentToFormField(
            WebDriverBy::id("$type-address1"),
            sprintf('%s %s',
                $person->address->street_number,
                $person->address->route
            )
        );
        $browser->addContentToFormField(
            WebDriverBy::id("$type-city"),
            $person->address->locality
        );

        $aalid  = strtoupper($person->address->country) == "UNITED STATES" ? "$type-state_id" : "$type-province";

        if(strtoupper($person->address->country) == "UNITED STATES") {
            $select = new WebDriverSelect(
                $browser->driver()->findElement(
                    WebDriverBy::id($aalid)
                )
            );
            $select->selectByVisibleText($person->address->administrative_area_level_1);
        } else {
            $browser->addContentToFormField(
                WebDriverBy::id($aalid),
                $person->address->administrative_area_level_1
            );
        }

        $browser->addContentToFormField(
            WebDriverBy::id("$type-zip"),
            $person->address->postal_code
        );
    }

    public static function fillOutFLECACSPP(HBIPerson $person, HBIBrowser $browser)
    {
        SELF::fillOutFLECACSP($person, $browser);

        $browser->addContentToFormField(
            WebDriverBy::id("phone"),
            $person->phone
        );

    }

    public static function fillOutFLECACSP(HBIPerson $person, HBIBrowser $browser)
    {
        $browser->addContentToFormField(
            WebDriverBy::id("first_name"),
            sprintf('TEST-%s', $person->name)
        );

        $browser->addContentToFormField(
            WebDriverBy::id("last_name"),
            sprintf('TEST-%s', $person->surname)
        );

        $browser->addContentToFormField(
            WebDriverBy::id("email"),
            sprintf('TEST-%s', $person->email)
        );

        // optin-country_id
        $select = new WebDriverSelect(
            $browser->driver()->findElement(
                WebDriverBy::id("optin-country_id")
            )
        );
        $select->selectByVisibleText(strtoupper($person->address->country));

        $browser->addContentToFormField(
            WebDriverBy::id("address1"),
            sprintf('%s %s',
                $person->address->street_number,
                $person->address->route
            )
        );
        $browser->addContentToFormField(
            WebDriverBy::id("city"),
            $person->address->locality
        );

        $aalid  = strtoupper($person->address->country) == "UNITED STATES" ? "optin-state_id" : "optin-province";

        if(strtoupper($person->address->country) == "UNITED STATES") {
            $select = new WebDriverSelect(
                $browser->driver()->findElement(
                    WebDriverBy::id($aalid)
                )
            );
            $select->selectByVisibleText($person->address->administrative_area_level_1);
        } else {
            $browser->addContentToFormField(
                WebDriverBy::id($aalid),
                $person->address->administrative_area_level_1
            );
        }

        $browser->addContentToFormField(
            WebDriverBy::id("zip"),
            $person->address->postal_code
        );

    }

    public static function fillOutCreditCardFormDetails(HBIPerson $person, HBIBrowser $browser)
    {
        $browser->addContentToFormField(
            WebDriverBy::id("cc_first_name"),
            sprintf('TEST-%s', $person->name)
        );

        $browser->addContentToFormField(
            WebDriverBy::id("cc_last_name"),
            sprintf('TEST-%s', $person->surname)
        );

        $browser->addContentToFormField(
            WebDriverBy::id("cc_number"),
            $person->card->number
        );

        $browser->addContentToFormField(
            WebDriverBy::id("cc_cvv"),
            $person->card->cvv
        );

        list($year, $month, $day) = explode('-', $person->card->expiration);

        $select = new WebDriverSelect(
            $browser->driver()->findElement(
                WebDriverBy::id("cc_month")
            )
        );
        $select->selectByValue($month);

        $select = new WebDriverSelect(
            $browser->driver()->findElement(
                WebDriverBy::id("cc_year")
            )
        );
        $select->selectByValue($year);

    }

    public static function createRandomPhoneNumber()
    {
        $number = rand(1111111111, 9999999999);
        $mask   = rand(1,8);

        // error_log( print_r(SELF::formatPhoneNumber($number, $mask),true) );

        return SELF::formatPhoneNumber($number, $mask);
    }

    /*********************************************************************/
    /*   Purpose: Return either masked phone number or false             */
    /*     Masks: Val=1 or xxx xxx xxxx                                             */
    /*            Val=2 or xxx xxx.xxxx                                             */
    /*            Val=3 or xxx.xxx.xxxx                                             */
    /*            Val=4 or (xxx) xxx xxxx                                           */
    /*            Val=5 or (xxx) xxx.xxxx                                           */
    /*            Val=6 or (xxx).xxx.xxxx                                           */
    /*            Val=7 or (xxx) xxx-xxxx                                           */
    /*            Val=8 or (xxx)-xxx-xxxx                                           */
    /*********************************************************************/
    public static function formatPhoneNumber($number, $mask)
    {
        // $val_num = SELF::validatePhoneNumber( $number );
        $val_num = true;

        if(!$val_num && !is_string ( $number ) ) {
            echo "Number $number is not a valid phone number! \n";
            return false;
        }   // end if !$val_num

        if(( $mask == 1 ) || ( $mask == 'xxx xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2 $3'." \n", $number);
            return $phone;
        }   // end if $mask == 1

        if(( $mask == 2 ) || ( $mask == 'xxx xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2.$3'." \n", $number);
            return $phone;
        }   // end if $mask == 2

        if(( $mask == 3 ) || ( $mask == 'xxx.xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1.$2.$3'." \n", $number);
            return $phone;
        }   // end if $mask == 3

        if(( $mask == 4 ) || ( $mask == '(xxx) xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2 $3'." \n", $number);
            return $phone;
        }   // end if $mask == 4

        if(( $mask == 5 ) || ( $mask == '(xxx) xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2.$3'." \n", $number);
            return $phone;
        }   // end if $mask == 5

        if(( $mask == 6 ) || ( $mask == '(xxx).xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1).$2.$3'." \n", $number);
            return $phone;
        }   // end if $mask == 6

        if(( $mask == 7 ) || ( $mask == '(xxx) xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2-$3'." \n", $number);
            return $phone;
        }   // end if $mask == 7

        if(( $mask == 8 ) || ( $mask == '(xxx)-xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1)-$2-$3'." \n", $number);
            return $phone;
        }

        return false;
    }

    /*********************************************************************/
    /*   Purpose:   To determine if the passed string is a valid phone  */
    /*              number following one of the establish formatting        */
    /*                  styles for phone numbers.  This function also breaks    */
    /*                  a valid number into it's respective components of:      */
    /*                          3-digit area code,                                      */
    /*                          3-digit exchange code,                                  */
    /*                          4-digit subscriber number                               */
    /*                  and validates the number against 10 digit US NANPA  */
    /*                  guidelines.                                                         */
    /*********************************************************************/
    public static function validatePhoneNumber($phone)
    {
        $format_pattern =   '/^(?:(?:\((?=\d{3}\)))?(\d{3})(?:(?<=\(\d{3})\))'.
                                    '?[\s.\/-]?)?(\d{3})[\s\.\/-]?(\d{4})\s?(?:(?:(?:'.
                                    '(?:e|x|ex|ext)\.?\:?|extension\:?)\s?)(?=\d+)'.
                                    '(\d+))?$/';
        $nanpa_pattern      =   '/^(?:1)?(?(?!(37|96))[2-9][0-8][0-9](?<!(11)))?'.
                                    '[2-9][0-9]{2}(?<!(11))[0-9]{4}(?<!(555(01([0-9]'.
                                    '[0-9])|1212)))$/';

        // Init array of variables to false
        $valid = array('format' => false,
                       'nanpa'  => false,
                       'ext'    => false,
                       'all'    => false);

        //Check data against the format analyzer
        if(preg_match ( $format_pattern, $phone, $matchset ) ) {
            $valid['format'] = true;
        }

        if(!$valid['format'] ) {
            return false;
        } else {
            //Set array of new components
            $components = array('ac' => $matchset[1], //area code
                                'xc' => $matchset[2], //exchange code
                                'sn' => $matchset[3]  //subscriber number
                                );

            // $components = array('ac' => $matchset[1], //area code
            //                     'xc' => $matchset[2], //exchange code
            //                     'sn' => $matchset[3], //subscriber number
            //                     'xn' => $matchset[4]  //extension number
            //                     );

            //Set array of number variants
            $numbers = array('original' => $matchset[0],
                             'stripped' => substr(preg_replace('[\D]',
                                                                '',
                                                                $matchset[0]), 0, 10)
                            );

            //Now let's check the first ten digits against NANPA standards
            if(preg_match($nanpa_pattern, $numbers['stripped'])) {
                $valid['nanpa'] = true;
            }

            //If the NANPA guidelines have been met, continue
            if($valid['nanpa']) {
                if(!empty($components['xn'])) {
                    if(preg_match( '/^[\d]{1,6}$/', $components['xn'])) {
                        $valid['ext'] = true;
                    }
                } else {
                    $valid['ext'] = true;
                }
            }

            //If the extension number is valid or non-existent, continue
            if($valid['ext']) {
                $valid['all'] = true;
            }
        }

        return $valid['all'];
    }

    public static function getPriceFromString($string)
    {
        preg_match('/\$([0-9]+[\.]*[0-9]*)/', $string, $match);

        return $match[1];
    }

    public static function verifyTotals()
    {
        // summary-subtotal
        // summary-shipping
        // summary-tax
        // summary-total
    }
}
