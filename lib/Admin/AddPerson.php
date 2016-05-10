<?php
namespace HBI\Admin;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

use HBI\Admin\Actions;
use HBI\HBIBrowser;
use HBI\HBIPeople;
use HBI\HBIHelper;
use HBI\HBIAddresses;

/**
*
*/
class AddPerson extends Actions
{
    private $person;
    private $address;
    private $contact;
    private $financial;

    function __construct(HBIBrowser $browser, $person=null, $type=null)
    {
        $this->browser      = $browser;
        $this->person       = !empty($person) ? $person : $this->defineRandomPerson();
        $this->person->type = !empty($type) ? $type : $this->defineRandomPersonType();
    }

    function __destruct()
    {
        // TODO: Change this into a dynamic parent method
        error_log( print_r($this->person, true) );
    }

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

    private function defineRandomPerson()
    {
        $people       = new HBIPeople;
        $collection   = $people->buildCollection(1);

        return $collection;
    }

    private function defineRandomPersonType()
    {
        $options = $this->browser->webui()->getHiddenOptions("user_types[]");
        $rand    = rand(0, count($options)-1);

        return $options[$rand];
    }

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

    private function addContactInfoToForm()
    {
        // Create New Contact Info
        $addresses  = new HBIAddresses;
        $collection = $addresses->buildCollection(1);
        $addr       = $collection[0];

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

    private function clickTab($tabname)
    {
        $this->browser->webui()->clickTab($tabname);
    }

    private function openAddPanel()
    {
        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');

        // Check to see if the element is visible
        $this->browser->driver()->wait(5, 1000)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('.people-add-modal-panel')
            )
        );
    }

    private function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton('.btn.btn-xs.btn-info.pull-right.mr20');
        sleep(1);
    }

    private function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(".btn.btn-default.btn-xs.pull-right.dismissButton");
        sleep(1);
    }

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

    private function refreshPage()
    {
        $this->browser->webui()->refreshPage();
    }

}
