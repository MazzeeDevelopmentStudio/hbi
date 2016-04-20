<?php
namespace HBI;

use \Facebook\WebDriver\Remote\DesiredCapabilities;
use \Facebook\WebDriver\Remote\RemoteWebDriver;
use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverElement;
use \Facebook\WebDriver\WebDriverWindow;
use \Facebook\WebDriver\WebDriverBy;
use \Facebook\WebDriver;

/**
*
*/
class HBIAddPerson
{
    private $_driver;
    private $_person;
    private $_person_type;
    private $_webui;

    function __construct($driver, $person, $person_type)
    {
        $this->_driver = $driver;
        $this->setPerson($person);
        $this->_person_type = $person_type;
        $this->_webui = new HBIWebUI($this->_driver);

    }

    function setPerson($person)
    {
        $this->_person = $person;
    }

    function getPerson()
    {
        return $this->_person;
    }

    public function addPersonDataToForm()
    {
        $access = sprintf('set%sAccess', $this->_person_type );

        $this->$access();
        $this->_webui->enterFieldData("first_name", "TEST-".$this->_person->first_name, "id");
        $this->_webui->enterFieldData("last_name", "TEST-".$this->_person->last_name, "id");
        $this->_webui->enterFieldData("emailnoprefill", HBIHelper::createRandomEmail($this->_person), "id");
        $this->_webui->enterFieldData("passwordnoprefill", "1234567890", "id");
    }

    public function addContactInfoToForm()
    {
        $this->_webui->enterFieldData("addresses[0][address1]", $this->_person->address, "id");
        $this->_webui->enterFieldData("addresses[0][city]", $this->_person->city, "id");
        $this->_webui->enterFieldData("addresses[0][zip]", $this->_person->postal_code, "id");
    }

    public function clickTab($tabname)
    {
        $this->_webui->clickTab($tabname);
    }

    public function openAddPanel()
    {
        // There is a timing issue so we need to wait a second
        sleep(1);
        // TODO: Add ID to "Add" button
        $this->_webui->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');

        // Check to see if the element is visible
        $this->_driver->wait(20, 1000)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('h3.panel-title')
            )
        );
        error_log( "The Add Panel is now Visible".PHP_EOL );

    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->_webui->clickButton('.btn.btn-xs.btn-info.pull-right.mr20');
        sleep(1);
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->_webui->clickButton(".btn.btn-default.btn-xs.pull-right.dismissButton");
        sleep(1);
    }

    function setCustomerAccess()
    {
        $this->_webui->clearField("s2id_autogen1","id");
        $this->_webui->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->_webui->enterFieldData("s2id_autogen1", "Customer", "id");
        $this->_webui->clickButton(".select2-match");
    }

    function setStaffAccess()
    {
        $this->_webui->clearField("s2id_autogen1","id");
        $this->_webui->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->_webui->enterFieldData("s2id_autogen1", "Sales Agent", "id");
        $this->_webui->clickButton(".select2-match");
    }

    public function refreshPage()
    {
        $this->_webui->refreshPage();
    }

}
