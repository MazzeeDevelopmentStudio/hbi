<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\HBIBrowser;
use HBI\HBIPeople;
use HBI\HBIHelper;
use HBI\HBIAddresses;
use HBI\HBIPerson;

/**
 *
 */
class AddPerson extends Actions
{
    private $browser;
    private $person;
    private $address;
    private $contact;
    private $financial;
    private $log;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $person=null, $type=null)
    {
        $this->browser      = $browser;
        $this->log          = $GLOBALS['HBILog'];
        $this->person       = !empty($person) ? $person : $this->defineRandomPerson();
        $this->person->type = !empty($type) ? $type : $this->defineRandomPersonType();
    }

    /**
     * [__destruct description]
     */
    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->person, true) );
    }

    /**
     * [run description]
     * @return [type] [description]
     */
    public function run()
    {
        $this->openAddPanel();
        $this->addPersonDataToForm();
        $this->clickTab("Address");
        $this->addContactInfoToForm();
        $this->clickTab("Contact");
        $this->clickSaveButton();

        $notification = $this->browser->panel()->waitForNotification();

        $this->clickDoneButton();
    }

    /**
     * [defineRandomPerson description]
     * @return [type] [description]
     */
    protected function defineRandomPerson()
    {
        $ppl              = new HBIPeople();
        $person           = $ppl->buildCollection(1);
        $person->email    = HBIHelper::createRandomEmail(null, true);
        $person->phone    = SELF::createRandomPhoneNumber();
        $person->password = HBIHelper::createPassword(5);

        $addr             = new HBIAddresses;
        $person->address  = $addr->buildCollection(1);

        unset($ppl);
        unset($addr);

        $this->testifyPersonDetails($person);

        $this->log->writeToLogFile($person);

        return $person;
    }

    /**
     * [defineRandomPersonType description]
     * @return [type] [description]
     */
    protected function defineRandomPersonType()
    {
        $options = $this->browser->webui()->getHiddenOptions("user_types[]");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }

    /**
     * [addPersonDataToForm description]
     */
    protected function addPersonDataToForm()
    {
        $this->setAccessLevel($this->person->type);

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("first_name"),
            $this->person->name
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("last_name"),
            $this->person->surname
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("emailnoprefill"),
            $this->person->email
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("passwordnoprefill"),
            $this->person->password
        );
    }

    /**
     * [addContactInfoToForm description]
     */
    protected function addContactInfoToForm()
    {
        print_r($this->person);
        $addr       = $this->person->address;

        $this->setContactState($addr->administrative_area_level_1);
        $this->setContactCountry($addr->country);

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][address1]"),
            sprintf('%s %s', $addr->street_number, $addr->route)
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][city]"),
            $addr->locality
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][zip]"),
            $addr->postal_code
        );
    }

    /**
     * [clickTab description]
     * @param  [type] $tabname [description]
     * @return [type]          [description]
     */
    protected function clickTab($tabname)
    {
        $this->browser->webui()->clickTab($tabname);
    }

    /**
     * [openAddPanel description]
     * @return [type] [description]
     */
    protected function openAddPanel()
    {
        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );

        // Check to see if the element is visible
        $this->browser->driver()->wait(20, 250)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.people-add-modal-panel')
            )
        );
    }

    /**
     * [clickSaveButton description]
     * @return [type] [description]
     */
    protected function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-xs.btn-info.pull-right.mr20')
        );
        sleep(1);
    }

    /**
     * [clickDoneButton description]
     * @return [type] [description]
     */
    protected function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".btn.btn-default.btn-xs.pull-right.dismissButton")
        );
        sleep(1);
    }

    /**
     * [setAccessLevel description]
     * @param [type] $level [description]
     */
    protected function setAccessLevel($level)
    {
        $this->browser->webui()->clearField("s2id_autogen1","id");
        $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            $level
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    /**
     * [setContactState description]
     * @param [type] $state [description]
     */
    protected function setContactState($state)
    {
        $this->browser->clickElement(
            WebDriverBy::id("select2-chosen-3")
        );
        $this->browser->webui()->clearField("s2id_autogen3_search","id");
        // $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen3_search"),
            $state
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    protected function setContactCountry($country)
    {
        $this->browser->clickElement(
            WebDriverBy::id("select2-chosen-2")
        );
        $this->browser->webui()->clearField("s2id_autogen2_search","id");

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen2_search"),
            $country
        );
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".select2-match")
        );
    }

    /**
     * [refreshPage description]
     * @return [type] [description]
     */
    protected function refreshPage()
    {
        $this->browser->webui()->refreshPage();
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
                    '$1 $2 $3', $number);
            return $phone;
        }

        if(( $mask == 2 ) || ( $mask == 'xxx xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1 $2.$3', $number);
            return $phone;
        }

        if(( $mask == 3 ) || ( $mask == 'xxx.xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '$1.$2.$3', $number);
            return $phone;
        }

        if(( $mask == 4 ) || ( $mask == '(xxx) xxx xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2 $3', $number);
            return $phone;
        }

        if(( $mask == 5 ) || ( $mask == '(xxx) xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2.$3', $number);
            return $phone;
        }

        if(( $mask == 6 ) || ( $mask == '(xxx).xxx.xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1).$2.$3', $number);
            return $phone;
        }

        if(( $mask == 7 ) || ( $mask == '(xxx) xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1) $2-$3', $number);
            return $phone;
        }

        if(( $mask == 8 ) || ( $mask == '(xxx)-xxx-xxxx' ) ) {
            $phone = preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~',
                    '($1)-$2-$3', $number);
            return $phone;
        }

        return false;
    }

    public static function testifyPersonDetails(HBIPerson &$person, $prefix="TEST-")
    {
        $person->name    = sprintf('%s%s', $prefix, $person->name);
        $person->surname = sprintf('%s%s', $prefix, $person->surname);
        $person->email   = sprintf('%s%s', $prefix, $person->email);
    }

}
