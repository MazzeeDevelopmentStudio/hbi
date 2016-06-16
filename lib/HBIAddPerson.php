<?php
namespace HBI;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver;

use HBI\Admin\Actions;

/**
*
*/
class HBIAddPerson extends Actions
{
    private $person;
    private $address;
    private $contact;
    private $financial;

    /**
     * [__construct description]
     * @param HBIBrowser $browser [description]
     * @param [type]     $person  [description]
     * @param [type]     $type    [description]
     */
    function __construct(HBIBrowser $browser, $person=null, $type=null)
    {
        $this->browser      = $browser;
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
    private function defineRandomPerson()
    {
        $people       = new HBIPeople;
        $collection   = $people->buildCollection(1);

        return $collection;
    }

    /**
     * [defineRandomPersonType description]
     * @return [type] [description]
     */
    private function defineRandomPersonType()
    {
        $options = $this->browser->webui()->getHiddenOptions("user_types[]");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }

    /**
     * [addPersonDataToForm description]
     */
    private function addPersonDataToForm()
    {
        $this->setAccessLevel($this->person->type);

        $this->person->email    = HBIHelper::createRandomEmail($this->person);
        $this->person->password = HBIHelper::createPassword(5);

        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("first_name"),
            "TEST-".$this->person->name
        );
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("last_name"),
            "TEST-".$this->person->surname
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
    private function addContactInfoToForm()
    {
        // Create New Contact Info
        $addresses  = new HBIAddresses;
        $collection = $addresses->buildCollection(1);
        $addr       = $collection;

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
    private function clickTab($tabname)
    {
        $this->browser->webui()->clickTab($tabname);
    }

    /**
     * [openAddPanel description]
     * @return [type] [description]
     */
    private function openAddPanel()
    {
        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');

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
    private function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton('.btn.btn-xs.btn-info.pull-right.mr20');
        sleep(1);
    }

    /**
     * [clickDoneButton description]
     * @return [type] [description]
     */
    private function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(".btn.btn-default.btn-xs.pull-right.dismissButton");
        sleep(1);
    }

    /**
     * [setAccessLevel description]
     * @param [type] $level [description]
     */
    private function setAccessLevel($level)
    {
        $this->browser->webui()->clearField("s2id_autogen1","id");
        $this->browser->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->browser->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            $level
        );
        $this->browser->webui()->clickButton(".select2-match");
    }

    /**
     * [refreshPage description]
     * @return [type] [description]
     */
    private function refreshPage()
    {
        $this->browser->webui()->refreshPage();
    }

}
