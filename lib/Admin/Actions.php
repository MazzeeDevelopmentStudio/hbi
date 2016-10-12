<?php
namespace HBI\Admin;

use HBI\HBIBrowser;

use \Facebook\WebDriver\WebDriverExpectedCondition;
use \Facebook\WebDriver\WebDriverBy;

/**
*
*/
class Actions
{
    private $browser;

    public function __construct(HBIBrowser $browser)
    {
        $this->browser = $browser;
    }


    public function openAddPanel()
    {
        // Need to wait for button
        $this->browser->waitForElement(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );

        // TODO: Add ID to "Add" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-primary.btn-xs.pull-right.mb20')
        );

        print("ACTION   : Clicked ADD PANEL Button".PHP_EOL);
    }

    public function clickSaveButton()
    {
        // TODO: Use Save button's ID
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector('.btn.btn-xs.btn-info.pull-right.mr20.btn-save')
        );
        print("ACTION   : Clicked SAVE Button".PHP_EOL);

        // TODO: Wait for Message to see if this was a success or failure
        
    }

    public function clickDoneButton()
    {
        // TODO: Add ID to "Done" button
        $this->browser->webui()->clickButton(
            WebDriverBy::cssSelector(".btn.btn-default.btn-xs.pull-right.btn-dismiss")
        );
        print("ACTION   : Clicked DONE Button".PHP_EOL);
    }

    public function refreshPage()
    {
        $this->browser->webui()->refreshPage();
        print("ACTION   : Refreshed Page View".PHP_EOL);
    }

}
