<?php
namespace HBI\Funnel;

use HBI\HBIBrowser;
use HBI\HBIHelper;
use HBI\HBIPeople;
use HBI\HBIPerson;
use HBI\HBIAddresses;
use HBI\HBICreditCards;
use HBI\HBICreditCardCreator;

use \facebook\WebDriver\Exception\NoSuchElementException;
use \Facebook\WebDriver\Exception\UnknownServerException;
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

    /**
     * [totalAmountTally description]
     * @return [type] [description]
     */
    public function totalAmountTally()
    {

    }

    /**
     * [getBillingAddressDetails description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     */
    public static function getBillingAddressDetails(HBIPerson $person)
    {
        $isBillingTheSame = (bool)rand(0,1);

        if($isBillingTheSame) {
            return $person;
        }

        $person = SElF::getPersonWithAddress();

        return $person;
    }

    /**
     * [getShippingAddressDetails description]
     * @param  HBIPerson $person [description]
     * @return [type]            [description]
     */
    public static function getShippingAddressDetails(HBIPerson $person)
    {
        $isBillingTheSame = (bool)rand(0,1);

        if($isBillingTheSame) {
            return $person;
        }

        $person = SElF::getPersonWithAddress();

        return $person;
    }

    /**
     * [getPerson description]
     * @return [type] [description]
     */
    public static function getPerson()
    {
        $ppl            = new HBIPeople();
        $person         = $ppl->buildCollection(1);
        $person->email  = HBIHelper::createRandomEmail(null, true);
        $person->phone  = SELF::createRandomPhoneNumber();

        return $person;
    }

    /**
     * [getPersonWithAddress description]
     * @return [type] [description]
     */
    public static function getPersonWithAddress()
    {
        $person           = SELF::getPerson();
        $addr             = new HBIAddresses;
        $person->address  = $addr->buildCollection(1);
        $person->contact  = null;

        return $person;
    }

    /**
     * [getPersonWithFullDetails description]
     * @return [type] [description]
     */
    public static function getPersonWithFullDetails()
    {
        $person        = SELF::getPersonWithAddress();
        $creator       = new HBICreditCardCreator;
        $person->card  = $creator->generate('random', 1);

        // Add International Address to the mix
        // This will be put into its more complete form later
        if($person->residency !== "United States" && !(bool)rand(0,5)) {
            $person->address->country = $person->residency;
        }

        return $person;
    }

    /**
     * [randomlySelectUpsells description]
     * @param  HBIBrowser $browser [description]
     * @return [type]              [description]
     */
    public static function randomlySelectUpsells(HBIBrowser $browser)
    {
        $selector  = !(bool)rand(0,4) ? 'input.responsive-img' : 'a.nothanks';
        $isPresent = WebDriverExpectedCondition::visibilityOfElementLocated(
                        WebDriverBy::cssSelector($selector)
                    );
        try {
            $browser->driver()->wait(5, 50)->until(
                $isClickable = WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::cssSelector($selector)
                )
            );

        } catch (NoSuchElementException $e) {
            return false;
        } catch (UnknownServerException  $e) {

        }

        if($isPresent && $isClickable) {
            $browser->clickElement(
                WebDriverBy::cssSelector($selector)
            );
            // TODO: Return usable info about upsell
            return $selector;
        }

        return false;

    }

    /**
     * [randomlySelectAddonsByIds description]
     * @param  HBIBrowser $browser  [description]
     * @param  Array      $addOnIds [description]
     * @return [type]               [description]
     */
    public static function randomlySelectAddonsByIds(HBIBrowser $browser, Array $addOnIds)
    {
        $idPrefix = "addon-checkbox_";
        $idCnt    = (int)count($addOnIds)*2;

        foreach ($addOnIds as $k => $id) {
            $lblForId = $idPrefix.$id;

            if( !(bool)rand(0, $idCnt) ) {
                try {
                    $browser->webui()->setCheckBoxCheckedState(
                        WebDriverBy::id($lblForId),
                        1
                    );
                } catch (NoSuchElementException $e) {
                    // Log it? Bug it?
                }
                continue;
            }
            try {
                $browser->webui()->setCheckBoxCheckedState(
                    WebDriverBy::id($lblForId),
                    0
                );
            } catch (NoSuchElementException $e) {
                // Log it? Bug it?
            } finally {
                unset($addOnIds[$k]);
            }
        }

        return $addOnIds;
    }

    /**
     * [randomlySelectAddons description]
     * @param  HBIBrowser $browser [description]
     * @return [type]              [description]
     */
    public static function randomlySelectAddons(HBIBrowser $browser)
    {
        print("FUNCTION randomlySelectAddons CALLED".PHP_EOL);
        $added = array();

        // OPT FOR ADDONS RANDOMALY
        $addons = $browser->driver()->findElements(
            WebDriverBy::cssSelector(".addon-checkbox.filled-in")
        );

        foreach ($addons as $addon) {
            if (rand(0, 1)) {
                $addid = $addon->getAttribute('id');
                $label = $browser->driver()->findElement(
                    WebDriverBy::cssSelector(
                        sprintf('label[for=%s]', $addid) )
                );

                $label->getLocationOnScreenOnceScrolledIntoView();
                $label->click();

                $added[] = $addid;
            }
        }

        return $added;
    }

    public static function createRandomPhoneNumber()
    {
        $number = rand(1111111111, 9999999999);
        $mask   = rand(1,8);

        return SELF::formatPhoneNumber($number, $mask);
    }

    /**
     * [formatPhoneNumber description]
     * @param  [type] $number [description]
     * @param  [type] $mask   [description]
     * @return [type]         [description]
     * TODO: Fix \n (return) issue
     */
    public static function formatPhoneNumber($number, $mask)
    {
        // $val_num = SELF::validatePhoneNumber( $number );
        $val_num = true;

        if(!$val_num && !is_string ( $number ) ) {
            echo "Number $number is not a valid phone number! \n";
            return false;
        }

        if(( $mask == 1 ) || ( $mask == 'xxx xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2 $3'." \n", $number);
            return $phone;
        }

        if(( $mask == 2 ) || ( $mask == 'xxx xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2.$3'." \n", $number);
            return $phone;
        }

        if(( $mask == 3 ) || ( $mask == 'xxx.xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1.$2.$3'." \n", $number);
            return $phone;
        }

        if(( $mask == 4 ) || ( $mask == '(xxx) xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2 $3'." \n", $number);
            return $phone;
        }

        if(( $mask == 5 ) || ( $mask == '(xxx) xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2.$3'." \n", $number);
            return $phone;
        }

        if(( $mask == 6 ) || ( $mask == '(xxx).xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1).$2.$3'." \n", $number);
            return $phone;
        }

        if(( $mask == 7 ) || ( $mask == '(xxx) xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2-$3'." \n", $number);
            return $phone;
        }

        if(( $mask == 8 ) || ( $mask == '(xxx)-xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1)-$2-$3'." \n", $number);
            return $phone;
        }

        return false;
    }

    /**
     * [validatePhoneNumber description]
     * @param  [type] $phone [description]
     * @return [type]        [description]
     */
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

    /**
     * [getPriceFromString description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    public static function getPriceFromString($string)
    {
        preg_match('/\$([0-9]+[\.]*[0-9]*)/', $string, $match);

        return $match[1];
    }

    /**
     * [verifyTotals description]
     * @return [type] [description]
     */
    public static function verifyTotals()
    {
        // summary-subtotal
        // summary-shipping
        // summary-tax
        // summary-total
    }

    /**
     * [testifyPersonDetails description]
     * @param  HBIPerson &$person [description]
     * @param  string    $prefix  [description]
     * @return [type]             [description]
     */
    public static function testifyPersonDetails(HBIPerson &$person, $prefix="TEST-")
    {
        $person->name    = sprintf('%s%s', $prefix, $person->name);
        $person->surname = sprintf('%s%s', $prefix, $person->surname);
        $person->email   = sprintf('%s%s', $prefix, $person->email);
    }
}
