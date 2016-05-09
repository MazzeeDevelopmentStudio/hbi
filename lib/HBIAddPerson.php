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
    private $ob;
    private $_person;
    private $_person_type;

    // function __construct($driver, $person, $person_type)
    function __construct(HBIOpenBrowser $ob, $person, $person_type)
    {
        $this->ob = $ob;
        $this->setPerson($person);
        $this->_person_type = $person_type;
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
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("first_name"),
            "TEST-".$this->_person->first_name
        );
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("last_name"),
            "TEST-".$this->_person->last_name
        );
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("emailnoprefill"),
            HBIHelper::createRandomEmail($this->_person)
        );
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("passwordnoprefill"),
            "1234567890"
        );
    }

    public function addContactInfoToForm()
    {
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][address1]"),
            $this->_person->address
        );
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][city]"),
            $this->_person->city
        );
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("addresses[0][zip]"),
            $this->_person->postal_code
        );
    }

    public function clickTab($tabname)
    {
        $this->ob->webui()->clickTab($tabname);
    }

    public function openAddPanel()
    {
        // There is a timing issue so we need to wait a second
        // sleep(1);
        // TODO: Add ID to "Add" button
        $this->ob->webui()->clickButton('.btn.btn-primary.btn-xs.pull-right.mb20');

        // Check to see if the element is visible
        $this->ob->driver()->wait(20, 1000)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::cssSelector('h3.panel-title')
            )
        );
    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->ob->webui()->clickButton('.btn.btn-xs.btn-info.pull-right.mr20');
        sleep(1);
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->ob->webui()->clickButton(".btn.btn-default.btn-xs.pull-right.dismissButton");
        sleep(1);
    }

    function setCustomerAccess()
    {
        $this->ob->webui()->clearField("s2id_autogen1","id");
        $this->ob->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            "Customer"
        );
        $this->ob->webui()->clickButton(".select2-match");
    }

    function setStaffAccess()
    {
        $this->ob->webui()->clearField("s2id_autogen1","id");
        $this->ob->webui()->removeInputedValue("a.select2-search-choice-close", "cssSelector");
        $this->ob->webui()->enterFieldData(
            WebDriverBy::id("s2id_autogen1"),
            "Sales Agent"
        );
        $this->ob->webui()->clickButton(".select2-match");
    }

    public function refreshPage()
    {
        $this->ob->webui()->refreshPage();
    }

}
