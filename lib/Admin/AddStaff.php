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
class AddStaff extends AddPerson
{
    private $browser;
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

        parent::__construct();
        $this->person->type = staff;
    }

    /**
     * [run description]
     * @return [type] [description]
     */
    public function run()
    {
        $this->openAddPanel();
        $this->addPersonDataToForm();
        $this->clickSaveButton();

        $notification = $this->browser->panel()->waitForNotification();

        $this->clickDoneButton();
    }

}
